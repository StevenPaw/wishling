<?php

namespace App\Auth;

use App\Model\LoginToken;
use App\Model\User;
use App\Model\UserSession;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBDatetime;
use Throwable;

/**
 * Eigenes Login-System per Magic Link (unabhängig von Silverstripe Members).
 *
 * Ablauf:
 *  1. requestLoginLink(): legt ein Einmal-Token an und verschickt den Link per Mail.
 *     Unbekannte E-Mail-Adressen werden beim ersten Login automatisch registriert.
 *  2. verifyLoginToken(): löst das Token ein und startet eine Session (HttpOnly-Cookie).
 */
class AuthService
{
    use Injectable;
    use Configurable;

    public const COOKIE_NAME = 'wishling_session';

    /** Gültigkeit eines Login-Links in Sekunden */
    private static int $token_lifetime = 900;

    /** Gültigkeit einer Session in Tagen */
    private static int $session_lifetime_days = 90;

    /** Zeitfenster für das Rate-Limiting in Sekunden */
    private static int $rate_limit_window = 900;

    /** Max. Login-Links pro E-Mail-Adresse im Zeitfenster */
    private static int $rate_limit_per_email = 3;

    /** Max. Login-Links pro IP im Zeitfenster */
    private static int $rate_limit_per_ip = 10;

    private ?User $currentUser = null;

    private bool $currentUserResolved = false;

    /**
     * @throws AuthException
     */
    public function requestLoginLink(string $email, string $ip, string $redirect = ''): void
    {
        $email = User::normalizeEmail($email);
        if (!User::isValidEmail($email)) {
            throw new AuthException('Bitte gib eine gültige E-Mail-Adresse ein.', 400);
        }

        $windowStart = $this->formatTimestamp($this->now() - static::config()->get('rate_limit_window'));
        $perEmail = LoginToken::get()->filter(['Email' => $email, 'Created:GreaterThan' => $windowStart])->count();
        $perIp = LoginToken::get()->filter(['RequestIP' => $ip, 'Created:GreaterThan' => $windowStart])->count();
        if ($perEmail >= static::config()->get('rate_limit_per_email')
            || $perIp >= static::config()->get('rate_limit_per_ip')
        ) {
            throw new AuthException('Zu viele Anfragen. Bitte warte ein paar Minuten und versuche es erneut.', 429);
        }

        $token = bin2hex(random_bytes(32));
        $loginToken = LoginToken::create();
        $loginToken->Email = $email;
        $loginToken->TokenHash = $this->hashToken($token);
        $loginToken->Expires = $this->formatTimestamp($this->now() + static::config()->get('token_lifetime'));
        $loginToken->RequestIP = $ip;
        $loginToken->write();

        $query = ['token' => $token];
        if ($this->isSafeRedirect($redirect)) {
            $query['weiter'] = $redirect;
        }
        $link = Director::absoluteURL('/login/bestaetigen?' . http_build_query($query));

        try {
            $mail = Email::create()
                ->setTo($email)
                ->setSubject('Dein Login-Link für Wishling')
                ->setHTMLTemplate('Email/LoginLinkEmail')
                ->setData([
                    'LoginLink' => $link,
                    'ValidMinutes' => (int) round(static::config()->get('token_lifetime') / 60),
                    'IsNewUser' => !User::get()->filter('Email', $email)->exists(),
                ]);
            $from = Environment::getEnv('WISHLING_MAIL_FROM');
            if ($from) {
                $mail->setFrom($from, 'Wishling');
            }
            $mail->send();
        } catch (Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->error('Login-Mail fehlgeschlagen: ' . $e->getMessage());
            $loginToken->delete();
            throw new AuthException('Die E-Mail konnte nicht verschickt werden. Bitte versuche es später erneut.', 500);
        }
    }

    /**
     * Löst einen Login-Link ein und startet eine Session.
     *
     * @throws AuthException
     */
    public function verifyLoginToken(string $token, HTTPRequest $request): User
    {
        $token = trim($token);
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            throw new AuthException('Dieser Login-Link ist ungültig.', 400);
        }

        /** @var LoginToken|null $loginToken */
        $loginToken = LoginToken::get()->filter('TokenHash', $this->hashToken($token))->first();
        if (!$loginToken || $loginToken->UsedAt) {
            throw new AuthException('Dieser Login-Link ist ungültig oder wurde bereits verwendet.', 400);
        }
        if (strtotime($loginToken->Expires) < $this->now()) {
            throw new AuthException('Dieser Login-Link ist abgelaufen. Bitte fordere einen neuen an.', 400);
        }

        $loginToken->UsedAt = $this->formatTimestamp($this->now());
        $loginToken->write();

        $user = User::get()->filter('Email', $loginToken->Email)->first();
        if (!$user) {
            $user = User::create();
            $user->Email = $loginToken->Email;
        }
        $user->LastLogin = $this->formatTimestamp($this->now());
        $user->write();

        $this->startSession($user, $request);
        $this->cleanup();

        return $user;
    }

    public function currentUser(HTTPRequest $request): ?User
    {
        if ($this->currentUserResolved) {
            return $this->currentUser;
        }
        $this->currentUserResolved = true;

        $session = $this->findSession($request);
        if (!$session) {
            return null;
        }

        // LastSeen nur gelegentlich schreiben, um unnötige DB-Writes zu sparen
        if (!$session->LastSeen || strtotime($session->LastSeen) < $this->now() - 300) {
            $session->LastSeen = $this->formatTimestamp($this->now());
            $session->write();
        }

        $user = $session->User();
        $this->currentUser = $user && $user->exists() ? $user : null;
        return $this->currentUser;
    }

    public function logout(HTTPRequest $request): void
    {
        $session = $this->findSession($request);
        if ($session) {
            $session->delete();
        }
        $this->setCookie(false, 0);
        $this->currentUser = null;
        $this->currentUserResolved = true;
    }

    private function startSession(User $user, HTTPRequest $request): void
    {
        $token = bin2hex(random_bytes(32));
        $days = static::config()->get('session_lifetime_days');

        $session = UserSession::create();
        $session->UserID = $user->ID;
        $session->TokenHash = $this->hashToken($token);
        $session->Expires = $this->formatTimestamp($this->now() + $days * 86400);
        $session->LastSeen = $this->formatTimestamp($this->now());
        $session->UserAgent = mb_substr((string) $request->getHeader('User-Agent'), 0, 255);
        $session->write();

        $this->setCookie($token, $days);
        $this->currentUser = $user;
        $this->currentUserResolved = true;
    }

    private function findSession(HTTPRequest $request): ?UserSession
    {
        $token = (string) Cookie::get(self::COOKIE_NAME);
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        /** @var UserSession|null $session */
        $session = UserSession::get()->filter('TokenHash', $this->hashToken($token))->first();
        if (!$session) {
            return null;
        }
        if (strtotime($session->Expires) < $this->now()) {
            $session->delete();
            return null;
        }
        return $session;
    }

    private function setCookie(string|false $value, int $days): void
    {
        Cookie::set(
            self::COOKIE_NAME,
            $value,
            $days,
            '/',
            null,
            Director::is_https(),
            true,
            Cookie::SAMESITE_LAX
        );
    }

    /**
     * Entfernt abgelaufene Tokens und Sessions
     */
    private function cleanup(): void
    {
        $cutoff = $this->formatTimestamp($this->now() - 86400);
        LoginToken::get()->filter('Expires:LessThan', $cutoff)->removeAll();
        UserSession::get()->filter('Expires:LessThan', $this->formatTimestamp($this->now()))->removeAll();
    }

    /**
     * Nur relative Pfade innerhalb der App (kein //host, kein Schema)
     */
    public function isSafeRedirect(string $redirect): bool
    {
        return $redirect !== ''
            && str_starts_with($redirect, '/')
            && !str_starts_with($redirect, '//')
            && !str_contains($redirect, '\\')
            && mb_strlen($redirect) <= 255;
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function now(): int
    {
        return DBDatetime::now()->getTimestamp();
    }

    private function formatTimestamp(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}
