<?php

namespace App\Control;

use App\Model\Wishlist;
use App\Model\Wish;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\ValidationException;
use Exception;

class ApiController extends Controller
{
    /**
     * Erlaubte Actions
     */
    private static $allowed_actions = [
        'index',
        'getData',
        'wishlists',
        'wishlist',
        'wishes',
        'wish',
        'create_wishlist',
        'create_wish',
        'update_wishlist',
        'update_wish',
        'delete_wishlist',
        'delete_wish',
        'archive_wish',
        'unarchive_wish',
    ];

    /**
     * URL-Handlers für diese Controller-Klasse
     */
    private static $url_handlers = [
        'wishlists/$Hash/wishes/$WishHash/archive' => 'archive_wish',
        'wishlists/$Hash/wishes/$WishHash/unarchive' => 'unarchive_wish',
        'wishlists/$Hash/wishes/$WishHash' => 'wish',
        'wishlists/$Hash/wishes' => 'wishes',
        'wishlists/all' => 'wishlists',
        'wishlists/$Hash' => 'wishlist',
        'wishlists' => 'wishlists',
        'data/$ID' => 'getData',
        '' => 'index',
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
            'version' => '1.0',
            'endpoints' => [
                'GET /api/wishlist' => 'Liste aller Wishlists',
                'GET /api/wishlist/wishlists' => 'Liste aller Wishlists',
                'POST /api/wishlist/wishlists' => 'Neue Wishlist erstellen',
                'GET /api/wishlist/wishlists/{hash}' => 'Einzelne Wishlist',
                'PUT /api/wishlist/wishlists/{hash}' => 'Wishlist bearbeiten',
                'DELETE /api/wishlist/wishlists/{hash}' => 'Wishlist löschen',
                'GET /api/wishlist/wishlists/{hash}/wishes' => 'Wishes einer Wishlist',
                'POST /api/wishlist/wishlists/{hash}/wishes' => 'Neuen Wish erstellen',
                'GET /api/wishlist/wishlists/{hash}/wishes/{hash}' => 'Einzelner Wish',
                'PUT /api/wishlist/wishlists/{hash}/wishes/{hash}' => 'Wish bearbeiten',
                'DELETE /api/wishlist/wishlists/{hash}/wishes/{hash}' => 'Wish löschen',
            ]
        ]);
    }

    /**
     * Liste aller Wishlists
     */
    public function wishlists(HTTPRequest $request)
    {
        $wishlists = Wishlist::get();
        return $this->jsonResponse($wishlists);
    }

    /**
     * Einzelne Wishlist anzeigen
     */
    public function wishlist(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishlist = Wishlist::find_one('Hash', $hash);

        if (!$wishlist) {
            throw new Exception('Wishlist nicht gefunden', 404);
        }

        return $this->jsonResponse($wishlist);
    }

    /**
     * Neue Wishlist erstellen
     */
    public function create_wishlist(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);
        $wishlist = new Wishlist();

        // Hier könnten Validierungen stattfinden

        $wishlist->update($data);

        try {
            $wishlist->write();
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }

        return $this->jsonResponse($wishlist, 201);
    }

    /**
     * Wishlist bearbeiten
     */
    public function update_wishlist(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishlist = Wishlist::find_one('Hash', $hash);

        if (!$wishlist) {
            throw new Exception('Wishlist nicht gefunden', 404);
        }

        $data = json_decode($request->getBody(), true);
        $wishlist->update($data);

        try {
            $wishlist->write();
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }

        return $this->jsonResponse($wishlist);
    }

    /**
     * Wishlist löschen
     */
    public function delete_wishlist(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishlist = Wishlist::find_one('Hash', $hash);

        if (!$wishlist) {
            throw new Exception('Wishlist nicht gefunden', 404);
        }

        $wishlist->delete();

        return $this->jsonResponse(['status' => 'ok']);
    }

    /**
     * Liste der Wishes einer Wishlist
     */
    public function wishes(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishlist = Wishlist::find_one('Hash', $hash);

        if (!$wishlist) {
            throw new Exception('Wishlist nicht gefunden', 404);
        }

        return $this->jsonResponse($wishlist->Wishes());
    }

    /**
     * Einzelner Wish anzeigen
     */
    public function wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishHash = $request->param('WishHash');
        $wishlist = Wishlist::find_one('Hash', $hash);

        if (!$wishlist) {
            throw new Exception('Wishlist nicht gefunden', 404);
        }

        $wish = Wish::get()->filter([
            'Hash' => $wishHash,
            'WishlistID' => $wishlist->ID,
        ])->first();

        if (!$wish) {
            throw new Exception('Wish nicht gefunden', 404);
        }

        return $this->jsonResponse($wish);
    }

    /**
     * Neuen Wish erstellen
     */
    public function create_wish(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);
        $wish = new Wish();

        // Hier könnten Validierungen stattfinden

        $wish->update($data);

        try {
            $wish->write();
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }

        return $this->jsonResponse($wish, 201);
    }

    /**
     * Wish bearbeiten
     */
    public function update_wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishHash = $request->param('WishHash');
        $wish = Wish::get()->filter([
            'Hash' => $wishHash,
        ])->first();

        if (!$wish) {
            throw new Exception('Wish nicht gefunden', 404);
        }

        $data = json_decode($request->getBody(), true);
        $wish->update($data);

        try {
            $wish->write();
        } catch (ValidationException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }

        return $this->jsonResponse($wish);
    }

    /**
     * Wish löschen
     */
    public function delete_wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishHash = $request->param('WishHash');
        $wish = Wish::get()->filter([
            'Hash' => $wishHash,
        ])->first();

        if (!$wish) {
            throw new Exception('Wish nicht gefunden', 404);
        }

        $wish->delete();

        return $this->jsonResponse(['status' => 'ok']);
    }

    /**
     * Wish archivieren
     */
    public function archive_wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishHash = $request->param('WishHash');
        $wish = Wish::get()->filter([
            'Hash' => $wishHash,
        ])->first();

        if (!$wish) {
            throw new Exception('Wish nicht gefunden', 404);
        }

        $wish->Archived = true;
        $wish->write();

        return $this->jsonResponse($wish);
    }

    /**
     * Wish wiederherstellen
     */
    public function unarchive_wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishHash = $request->param('WishHash');
        $wish = Wish::get()->filter([
            'Hash' => $wishHash,
        ])->first();

        if (!$wish) {
            throw new Exception('Wish nicht gefunden', 404);
        }

        $wish->Archived = false;
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
