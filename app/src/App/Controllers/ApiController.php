<?php

namespace App\Controllers;

use App\Model\Wishlist;
use App\Model\Wish;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use Exception;
use SilverStripe\Core\Validation\ValidationException;

class ApiController extends Controller
{
    /**
     * Erlaubte Actions
     */
    private static $allowed_actions = [
        'index',
        'getwishlist',
        'addwishlist',
        'archivewishlist',
        'getwish',
        'addwish',
        'archivewish',
    ];

    /**
     * Init-Methode
     */
    protected function init()
    {
        parent::init();
        $this->getResponse()->addHeader('Access-Control-Allow-Origin', '*');
        $this->getResponse()->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->getResponse()->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        if ($this->getRequest()->httpMethod() === 'OPTIONS') {
            return $this->jsonResponse(['status' => 'ok'], 200);
        }
    }

    /**
     * Index Action
     */
    public function index(HTTPRequest $request)
    {
        return $this->jsonResponse([
            'name' => 'Wishling API',
            'version' => '2.0',
            'endpoints' => [
                'POST /api/getwishlist' => 'Wishlist abrufen (Payload: Hash oder OwnerHash)',
                'POST /api/addwishlist' => 'Neue Wishlist erstellen',
                'POST /api/archivewishlist' => 'Wishlist archivieren (Payload: Hash oder OwnerHash)',
                'POST /api/getwish' => 'Wish abrufen (Payload: Hash)',
                'POST /api/addwish' => 'Neuen Wish erstellen (Payload: OwnerHash + Wish-Daten)',
                'POST /api/archivewish' => 'Wish archivieren (Payload: Hash)',
            ]
        ]);
    }

    /**
     * Wishlist abrufen
     * POST /api/getwishlist
     * Payload: { "Hash": "..." } oder { "OwnerHash": "..." }
     */
    public function getwishlist(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (isset($data['Hash'])) {
            $wishlist = Wishlist::get()->filter('Hash', $data['Hash'])->first();
        } elseif (isset($data['OwnerHash'])) {
            $wishlist = Wishlist::get()->filter('OwnerHash', $data['OwnerHash'])->first();
        } else {
            return $this->jsonError('Hash oder OwnerHash erforderlich', 400);
        }

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        return $this->jsonResponse($wishlist);
    }

    /**
     * Neue Wishlist erstellen
     * POST /api/addwishlist
     * Payload: { "Title": "...", "Description": "..." }
     */
    public function addwishlist(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);
        $wishlist = new Wishlist();
        $wishlist->update($data);

        try {
            $wishlist->write();
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }

        return $this->jsonResponse($wishlist, 201);
    }

    /**
     * Wishlist archivieren
     * POST /api/archivewishlist
     * Payload: { "Hash": "..." } oder { "OwnerHash": "..." }
     */
    public function archivewishlist(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (isset($data['Hash'])) {
            $wishlist = Wishlist::get()->filter('Hash', $data['Hash'])->first();
        } elseif (isset($data['OwnerHash'])) {
            $wishlist = Wishlist::get()->filter('OwnerHash', $data['OwnerHash'])->first();
        } else {
            return $this->jsonError('Hash oder OwnerHash erforderlich', 400);
        }

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        $wishlist->Archived = true;
        $wishlist->write();

        return $this->jsonResponse($wishlist);
    }

    /**
     * Wish abrufen
     * POST /api/getwish
     * Payload: { "Hash": "..." }
     */
    public function getwish(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['Hash'])) {
            return $this->jsonError('Hash erforderlich', 400);
        }

        $wish = Wish::get()->filter('Hash', $data['Hash'])->first();

        if (!$wish) {
            return $this->jsonError('Wish nicht gefunden', 404);
        }

        return $this->jsonResponse($wish);
    }

    /**
     * Neuen Wish erstellen
     * POST /api/addwish
     * Payload: { "OwnerHash": "...", "Title": "...", "Description": "...", "Price": "...", "URL": "..." }
     */
    public function addwish(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['OwnerHash'])) {
            return $this->jsonError('OwnerHash erforderlich', 400);
        }

        $wishlist = Wishlist::get()->filter('OwnerHash', $data['OwnerHash'])->first();

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        $wish = new Wish();
        $wish->ParentID = $wishlist->ID;
        $wish->Title = $data['Title'] ?? '';
        $wish->Description = $data['Description'] ?? '';
        $wish->Price = $data['Price'] ?? null;
        $wish->URL = $data['URL'] ?? '';

        try {
            $wishID = $wish->write();
            $wish = Wish::get()->byID($wishID);
            return $this->jsonResponse([
                'success' => true,
                'wish' => $wish->toMap(),
            ], 201);
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }    /**
     * Wish archivieren
     * POST /api/archivewish
     * Payload: { "Hash": "..." }
     */
    public function archivewish(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['Hash'])) {
            return $this->jsonError('Hash erforderlich', 400);
        }

        $wish = Wish::get()->filter('Hash', $data['Hash'])->first();

        if (!$wish) {
            return $this->jsonError('Wish nicht gefunden', 404);
        }

        $wish->Archived = true;
        $wish->write();

        return $this->jsonResponse($wish);
    }

    /**
     * Hilfsmethode für JSON-Responses
     */
    protected function jsonResponse($data, $code = 200)
    {
        $response = HTTPResponse::create(json_encode($data), $code);
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Hilfsmethode für JSON-Fehlerresponses
     */
    protected function jsonError($message, $code = 400)
    {
        return $this->jsonResponse(['error' => $message], $code);
    }

    /**
     * Link-Methode für URL-Generierung
     */
    public function Link($action = null)
    {
        return Controller::join_links('api', $action);
    }
}
