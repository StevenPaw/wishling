<?php

namespace App\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * ErrorController - Behandelt HTTP-Fehler (404, 500, etc.)
 *
 * Ersetzt das silverstripe/errorpage Modul
 */
class ErrorController extends Controller
{
    private static $allowed_actions = [
        'notFound',
        'serverError',
    ];

    /**
     * 404 Not Found
     */
    public function notFound(HTTPRequest $request)
    {
        $response = HTTPResponse::create();
        $response->setStatusCode(404);
        $response->setBody($this->customise([
            'Title' => '404 - Seite nicht gefunden',
            'Message' => 'Die angeforderte Seite konnte nicht gefunden werden.',
        ])->renderWith(['App\\Control\\ErrorController_404', 'App\\Control\\ErrorController']));

        return $response;
    }

    /**
     * 500 Server Error
     */
    public function serverError(HTTPRequest $request)
    {
        $response = HTTPResponse::create();
        $response->setStatusCode(500);
        $response->setBody($this->customise([
            'Title' => '500 - Serverfehler',
            'Message' => 'Es ist ein interner Serverfehler aufgetreten.',
        ])->renderWith(['App\\Control\\ErrorController_500', 'App\\Control\\ErrorController']));

        return $response;
    }

    /**
     * Link-Methode für URL-Generierung
     */
    public function Link($action = null)
    {
        return Controller::join_links('error', $action);
    }
}
