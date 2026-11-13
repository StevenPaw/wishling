<?php

namespace App\Api;

use App\Auth\AuthService;
use App\Model\User;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;

/**
 * Basis für alle JSON-Endpunkte.
 *
 * - Antwortet immer mit JSON (auch bei Fehlern)
 * - Schreibende Requests müssen den Header "X-Requested-With: XMLHttpRequest" senden.
 *   Zusammen mit dem SameSite=Lax-Cookie und fehlenden CORS-Freigaben schützt das vor CSRF,
 *   da fremde Seiten diesen Header nicht ohne CORS-Preflight setzen können.
 */
abstract class ApiController extends Controller
{
    private ?array $jsonBody = null;

    public function handleRequest(HTTPRequest $request): HTTPResponse
    {
        if (!in_array($request->httpMethod(), ['GET', 'HEAD'], true)
            && $request->getHeader('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            return $this->jsonError('Ungültige Anfrage.', 403);
        }

        $response = parent::handleRequest($request);

        // Framework-Fehler (404 für unbekannte Routen etc.) ebenfalls als JSON ausliefern
        if ($response->isError() && !str_contains((string) $response->getHeader('Content-Type'), 'application/json')) {
            $response = $this->jsonError($this->defaultErrorMessage($response->getStatusCode()), $response->getStatusCode());
        }

        $response->addHeader('Cache-Control', 'no-store');
        return $response;
    }

    protected function json(mixed $data, int $status = 200): HTTPResponse
    {
        $response = HTTPResponse::create(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $status);
        $response->addHeader('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }

    protected function jsonError(string $message, int $status = 400, array $extra = []): HTTPResponse
    {
        return $this->json(['error' => $message] + $extra, $status);
    }

    /**
     * Bricht die Verarbeitung ab und liefert einen JSON-Fehler aus.
     */
    protected function fail(string $message, int $status = 400): never
    {
        throw new HTTPResponse_Exception($this->jsonError($message, $status));
    }

    /**
     * JSON-Body des Requests als Array
     */
    protected function body(): array
    {
        if ($this->jsonBody === null) {
            $raw = (string) $this->getRequest()->getBody();
            $data = $raw === '' ? [] : json_decode($raw, true);
            if (!is_array($data)) {
                $this->fail('Ungültiges JSON.', 400);
            }
            $this->jsonBody = $data;
        }
        return $this->jsonBody;
    }

    /**
     * Liest ein Textfeld aus dem Body, trimmt es und prüft die Maximallänge.
     * Gibt null zurück, wenn das Feld nicht gesendet wurde.
     */
    protected function stringField(string $key, int $maxLength, string $label): ?string
    {
        $body = $this->body();
        if (!array_key_exists($key, $body)) {
            return null;
        }
        $value = $body[$key];
        if ($value === null) {
            return '';
        }
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->fail("{$label} hat ein ungültiges Format.", 400);
        }
        $value = trim((string) $value);
        if (mb_strlen($value) > $maxLength) {
            $this->fail("{$label} darf höchstens {$maxLength} Zeichen lang sein.", 400);
        }
        return $value;
    }

    protected function currentUser(): ?User
    {
        return AuthService::singleton()->currentUser($this->getRequest());
    }

    protected function requireUser(): User
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->fail('Bitte melde dich an.', 401);
        }
        return $user;
    }

    private function defaultErrorMessage(int $status): string
    {
        return match ($status) {
            401 => 'Bitte melde dich an.',
            403 => 'Keine Berechtigung.',
            404 => 'Nicht gefunden.',
            405 => 'Methode nicht erlaubt.',
            default => 'Es ist ein Fehler aufgetreten.',
        };
    }
}
