<?php

namespace App\Api;

use App\Auth\AuthException;
use App\Auth\AuthService;
use App\Service\AvatarService;
use RuntimeException;
use SilverStripe\Control\HTTPResponse;

/**
 * /api/auth
 *
 *   GET  me       aktueller User (oder null)
 *   PUT  me       Profil aktualisieren { displayName }
 *   POST me/avatar   Profilbild hochladen (multipart, Feld "image")
 *   DELETE me/avatar Profilbild entfernen
 *   POST request  Login-Link anfordern { email, redirect? }
 *   POST verify   Login-Link einlösen  { token }
 *   POST logout   Abmelden
 */
class AuthApiController extends ApiController
{
    private static $url_handlers = [
        'POST me/avatar' => 'uploadAvatar',
        'DELETE me/avatar' => 'deleteAvatar',
        'GET me' => 'showMe',
        'PUT me' => 'updateMe',
        'POST request' => 'requestLink',
        'POST verify' => 'verify',
        'POST logout' => 'logout',
    ];

    private static $allowed_actions = [
        'showMe',
        'uploadAvatar',
        'deleteAvatar',
        'updateMe',
        'requestLink',
        'verify',
        'logout',
    ];

    public function showMe(): HTTPResponse
    {
        $user = $this->currentUser();
        return $this->json(['user' => $user?->toApi()]);
    }

    public function updateMe(): HTTPResponse
    {
        $user = $this->requireUser();

        $displayName = $this->stringField('displayName', 100, 'Der Name');
        if ($displayName !== null) {
            if ($displayName === '') {
                $this->fail('Bitte gib einen Namen ein.', 400);
            }
            $user->DisplayName = $displayName;
        }
        $user->write();

        return $this->json(['user' => $user->toApi()]);
    }

    public function uploadAvatar(): HTTPResponse
    {
        $user = $this->requireUser();

        $upload = $this->getRequest()->postVar('image');
        if (!is_array($upload) || !isset($upload['tmp_name'], $upload['error'])) {
            $this->fail('Bitte wähle ein Bild aus.', 400);
        }
        if ((int) $upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
            $this->fail('Das Bild konnte nicht hochgeladen werden.', 400);
        }

        try {
            AvatarService::singleton()->store($user, (string) file_get_contents($upload['tmp_name']));
        } catch (RuntimeException $e) {
            $this->fail($e->getMessage(), 400);
        }

        return $this->json(['user' => $user->toApi()]);
    }

    public function deleteAvatar(): HTTPResponse
    {
        $user = $this->requireUser();
        AvatarService::singleton()->remove($user);

        return $this->json(['user' => $user->toApi()]);
    }

    public function requestLink(): HTTPResponse
    {
        $email = (string) $this->stringField('email', 254, 'Die E-Mail-Adresse');
        $redirect = (string) $this->stringField('redirect', 255, 'Die Weiterleitung');

        try {
            AuthService::singleton()->requestLoginLink($email, (string) $this->getRequest()->getIP(), $redirect);
        } catch (AuthException $e) {
            return $this->jsonError($e->getMessage(), $e->getCode() ?: 400);
        }

        return $this->json(['success' => true]);
    }

    public function verify(): HTTPResponse
    {
        $token = (string) $this->stringField('token', 128, 'Das Token');

        try {
            $user = AuthService::singleton()->verifyLoginToken($token, $this->getRequest());
        } catch (AuthException $e) {
            return $this->jsonError($e->getMessage(), $e->getCode() ?: 400);
        }

        return $this->json(['user' => $user->toApi()]);
    }

    public function logout(): HTTPResponse
    {
        AuthService::singleton()->logout($this->getRequest());
        return $this->json(['success' => true]);
    }
}
