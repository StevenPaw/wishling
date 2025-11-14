<?php

namespace App\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\View\Requirements;

/**
 * HomeController - Beispiel für einen Controller ohne SiteTree
 *
 * Dieser Controller verarbeitet die Root-URL deiner Webapp
 */
class HomeController extends Controller
{
    /**
     * Erlaubte Actions
     */
    private static $allowed_actions = [
        'index',
    ];

    /**
     * URL-Handlers für diese Controller-Klasse
     */
    private static $url_handlers = [
        '' => 'index',
    ];

    /**
     * Initialisierung - wird vor jeder Action ausgeführt
     */
    protected function init()
    {
        parent::init();

        // Hier kannst du CSS/JS-Dateien einbinden
        // Requirements::css('app/client/dist/css/app.css');
        // Requirements::javascript('app/client/dist/js/app.js');
    }

    /**
     * Index Action - Standard-Route
     */
    public function index(HTTPRequest $request)
    {
        // Du kannst Daten an dein Template übergeben
        return $this->render([
            'Title' => 'Willkommen',
            'Content' => 'Dies ist deine Webapp ohne SiteTree',
        ]);
    }

    /**
     * Link-Methode für URL-Generierung
     */
    public function Link($action = null)
    {
        return Controller::join_links('/', $action);
    }
}
