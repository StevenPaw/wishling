<?php

namespace App\Controllers;

use App\Model\Wishlist;
use App\Model\Wish;
use App\Model\WishReservation;
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
        'updatewishlist',
        'archivewishlist',
        'getwish',
        'addwish',
        'updatewish',
        'archivewish',
        'reservewish',
        'unreservewish',
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
                'POST /api/updatewishlist' => 'Wishlist aktualisieren (Payload: OwnerHash + Felder)',
                'POST /api/archivewishlist' => 'Wishlist archivieren (Payload: Hash oder OwnerHash)',
                'POST /api/getwish' => 'Wish abrufen (Payload: Hash)',
                'POST /api/addwish' => 'Neuen Wish erstellen (Payload: OwnerHash + Wish-Daten)',
                'POST /api/updatewish' => 'Wish aktualisieren (Payload: Hash + Felder)',
                'POST /api/archivewish' => 'Wish archivieren (Payload: Hash)',
                'POST /api/reservewish' => 'Wish reservieren (Payload: Hash + GuestName + Message)',
                'POST /api/unreservewish' => 'Wish-Reservierung aufheben (Payload: Hash + ReservationID)',
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
     * Wishlist aktualisieren
     * POST /api/updatewishlist
     * Payload: { "OwnerHash": "...", "Title": "...", "Description": "...", "Username": "..." }
     */
    public function updatewishlist(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['OwnerHash'])) {
            return $this->jsonError('OwnerHash erforderlich', 400);
        }

        $wishlist = Wishlist::get()->filter('OwnerHash', $data['OwnerHash'])->first();

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        if (isset($data['Title'])) $wishlist->Title = $data['Title'];
        if (isset($data['Description'])) $wishlist->Description = $data['Description'];
        if (isset($data['Username'])) $wishlist->Username = $data['Username'];
        if (isset($data['DefaultSort'])) $wishlist->DefaultSort = $data['DefaultSort'];

        try {
            $wishlist->write();
            return $this->jsonResponse([
                'success' => true,
                'wishlist' => $wishlist->toMap()
            ]);
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
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
        // Price: leere Strings als NULL speichern
        $wish->Price = !empty($data['Price']) ? $data['Price'] : null;
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
    }

    /**
     * Wish aktualisieren
     * POST /api/updatewish
     * Payload: { "Hash": "...", "Title": "...", "Description": "...", "Price": "...", "URL": "..." }
     */
    public function updatewish(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['Hash'])) {
            return $this->jsonError('Hash erforderlich', 400);
        }

        $wish = Wish::get()->filter('Hash', $data['Hash'])->first();

        if (!$wish) {
            return $this->jsonError('Wish nicht gefunden', 404);
        }

        if (isset($data['Title'])) $wish->Title = $data['Title'];
        if (isset($data['Description'])) $wish->Description = $data['Description'];
        if (isset($data['Price'])) $wish->Price = !empty($data['Price']) ? $data['Price'] : null;
        if (isset($data['URL'])) $wish->URL = $data['URL'];

        try {
            $wish->write();
            return $this->jsonResponse([
                'success' => true,
                'wish' => $wish->toMap()
            ]);
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    /**
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

        return $this->jsonResponse([
            'success' => true,
            'wish' => $wish->toMap()
        ]);
    }

    /**
     * Wish reservieren
     * POST /api/reservewish
     * Payload: { "Hash": "...", "GuestName": "...", "Message": "..." }
     */
    public function reservewish(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['Hash'])) {
            return $this->jsonError('Hash erforderlich', 400);
        }

        if (!isset($data['GuestName']) || empty(trim($data['GuestName']))) {
            return $this->jsonError('Gastname erforderlich', 400);
        }

        $wish = Wish::get()->filter('Hash', $data['Hash'])->first();

        if (!$wish) {
            return $this->jsonError('Wish nicht gefunden', 404);
        }

        // Prüfe ob bereits reserviert
        if ($wish->Reservations()->exists()) {
            return $this->jsonError('Dieser Wunsch wurde bereits reserviert', 400);
        }

        $reservation = new WishReservation();
        $reservation->ParentID = $wish->ID;
        $reservation->GuestName = trim($data['GuestName']);
        $reservation->Message = isset($data['Message']) ? trim($data['Message']) : '';

        try {
            $reservation->write();
            return $this->jsonResponse([
                'success' => true,
                'reservation' => [
                    'id' => $reservation->ID,
                    'guestName' => $reservation->GuestName,
                    'message' => $reservation->Message,
                ]
            ]);
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    /**
     * Wish-Reservierung aufheben
     * POST /api/unreservewish
     * Payload: { "Hash": "...", "ReservationID": ... }
     */
    public function unreservewish(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);

        if (!isset($data['Hash'])) {
            return $this->jsonError('Hash erforderlich', 400);
        }

        if (!isset($data['ReservationID'])) {
            return $this->jsonError('ReservationID erforderlich', 400);
        }

        $wish = Wish::get()->filter('Hash', $data['Hash'])->first();

        if (!$wish) {
            return $this->jsonError('Wish nicht gefunden', 404);
        }

        $reservation = WishReservation::get()->byID($data['ReservationID']);

        if (!$reservation || $reservation->ParentID != $wish->ID) {
            return $this->jsonError('Reservierung nicht gefunden', 404);
        }

        try {
            $reservation->delete();
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Reservierung wurde aufgehoben'
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
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
