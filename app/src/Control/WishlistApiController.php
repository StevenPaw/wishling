<?php

namespace App\Control;

use App\Model\Wishlist;
use App\Model\Wish;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\ValidationException;
use Exception;

/**
 * WishlistApiController - RESTful API für Wishlists und Wishes
 */
class WishlistApiController extends Controller
{
    private static $allowed_actions = [
        'index',
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
    ];

    private static $url_handlers = [
        'wishlists/$Hash/wishes/$WishHash' => 'wish',
        'wishlists/$Hash/wishes' => 'wishes',
        'wishlists/$Hash' => 'wishlist',
        'wishlists' => 'wishlists',
        '' => 'index',
    ];

    protected function init()
    {
        parent::init();

        // CORS Headers für Frontend
        $this->getResponse()->addHeader('Access-Control-Allow-Origin', '*');
        $this->getResponse()->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->getResponse()->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        // Handle OPTIONS Preflight
        if ($this->getRequest()->httpMethod() === 'OPTIONS') {
            return $this->jsonResponse(['status' => 'ok'], 200);
        }
    }

    /**
     * API Übersicht
     */
    public function index(HTTPRequest $request)
    {
        return $this->jsonResponse([
            'name' => 'Wishling API',
            'version' => '1.0',
            'debug' => [
                'action' => $request->param('Action'),
                'hash' => $request->param('Hash'),
                'wishHash' => $request->param('WishHash'),
                'url' => $request->getURL(),
                'method' => $request->httpMethod(),
            ],
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
     * GET /api/wishlist/wishlists - Liste aller Wishlists
     * POST /api/wishlist/wishlists - Neue Wishlist erstellen
     */
    public function wishlists(HTTPRequest $request)
    {
        $method = $request->httpMethod();

        if ($method === 'GET') {
            return $this->listWishlists($request);
        } elseif ($method === 'POST') {
            return $this->createWishlist($request);
        }

        return $this->jsonError('Method not allowed', 405);
    }

    /**
     * Liste aller Wishlists abrufen
     */
    protected function listWishlists(HTTPRequest $request)
    {
        try {
            $wishlists = Wishlist::get()->filter('Active', true);
            $data = [];

            foreach ($wishlists as $wishlist) {
                $data[] = $wishlist->toArray();
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Fehler beim Laden der Wishlists', 500);
        }
    }

    /**
     * Neue Wishlist erstellen
     */
    protected function createWishlist(HTTPRequest $request)
    {
        try {
            $data = json_decode($request->getBody(), true);

            if (!$data) {
                return $this->jsonError('Ungültiger JSON', 400);
            }

            $wishlist = Wishlist::create();
            $wishlist->Title = $data['title'] ?? '';
            $wishlist->Username = $data['username'] ?? '';
            $wishlist->Description = $data['description'] ?? '';
            $wishlist->Active = $data['active'] ?? true;
            $wishlist->Hash = $this->generateHash();

            $wishlist->write();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Wishlist erstellt',
                'data' => $wishlist->toArray()
            ], 201);

        } catch (ValidationException $e) {
            return $this->jsonError('Validierungsfehler: ' . $e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->jsonError('Fehler beim Erstellen der Wishlist', 500);
        }
    }

    /**
     * GET /api/wishlist/wishlists/{hash} - Einzelne Wishlist
     */
    public function wishlist(HTTPRequest $request)
    {
        $hash = $request->param('Hash');

        if (!$hash) {
            return $this->jsonError('Hash-Parameter erforderlich', 400);
        }

        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $wishlist->toArray()
        ]);
    }

    /**
     * POST /api/wishlist/wishlists - Neue Wishlist erstellen
     */
    public function create_wishlist(HTTPRequest $request)
    {
        try {
            $data = json_decode($request->getBody(), true);

            if (!$data) {
                return $this->jsonError('Ungültiger JSON', 400);
            }

            $wishlist = Wishlist::create();
            $wishlist->Title = $data['title'] ?? '';
            $wishlist->Username = $data['username'] ?? '';
            $wishlist->Description = $data['description'] ?? '';
            $wishlist->Active = $data['active'] ?? true;
            $wishlist->Hash = $this->generateHash();

            $wishlist->write();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Wishlist erstellt',
                'data' => $wishlist->toArray()
            ], 201);

        } catch (ValidationException $e) {
            return $this->jsonError('Validierungsfehler: ' . $e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->jsonError('Fehler beim Erstellen der Wishlist', 500);
        }
    }

    /**
     * PUT /api/wishlist/wishlists/{hash} - Wishlist bearbeiten
     */
    public function update_wishlist(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        try {
            $data = json_decode($request->getBody(), true);

            if (isset($data['title'])) $wishlist->Title = $data['title'];
            if (isset($data['username'])) $wishlist->Username = $data['username'];
            if (isset($data['description'])) $wishlist->Description = $data['description'];
            if (isset($data['active'])) $wishlist->Active = $data['active'];

            $wishlist->write();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Wishlist aktualisiert',
                'data' => $wishlist->toArray()
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Fehler beim Aktualisieren der Wishlist', 500);
        }
    }

    /**
     * DELETE /api/wishlist/wishlists/{hash} - Wishlist löschen
     */
    public function delete_wishlist(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        try {
            $wishlist->delete();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Wishlist gelöscht'
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Fehler beim Löschen der Wishlist', 500);
        }
    }

    /**
     * GET /api/wishlist/wishlists/{hash}/wishes - Wishes einer Wishlist
     */
    public function wishes(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        $wishes = $wishlist->Wishes();
        $data = [];

        foreach ($wishes as $wish) {
            $data[] = $wish->toArray();
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
            'count' => count($data),
            'wishlist' => $wishlist->toArray()
        ]);
    }

    /**
     * GET /api/wishlist/wishlists/{hash}/wishes/{wishHash} - Einzelner Wish
     */
    public function wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishHash = $request->param('WishHash');

        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();
        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        $wish = $wishlist->Wishes()->filter('Hash', $wishHash)->first();
        if (!$wish) {
            return $this->jsonError('Wish nicht gefunden', 404);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $wish->toArray()
        ]);
    }

    /**
     * POST /api/wishlist/wishlists/{hash}/wishes - Neuen Wish erstellen
     */
    public function create_wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();

        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        try {
            $data = json_decode($request->getBody(), true);

            if (!$data) {
                return $this->jsonError('Ungültiger JSON', 400);
            }

            $wish = Wish::create();
            $wish->Title = $data['title'] ?? '';
            $wish->Description = $data['description'] ?? '';
            $wish->Price = $data['price'] ?? 0;
            $wish->URL = $data['url'] ?? '';
            $wish->Hash = $this->generateHash();
            $wish->ParentID = $wishlist->ID;

            $wish->write();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Wish erstellt',
                'data' => $wish->toArray()
            ], 201);

        } catch (ValidationException $e) {
            return $this->jsonError('Validierungsfehler: ' . $e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->jsonError('Fehler beim Erstellen des Wishes', 500);
        }
    }

    /**
     * PUT /api/wishlist/wishlists/{hash}/wishes/{wishHash} - Wish bearbeiten
     */
    public function update_wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishHash = $request->param('WishHash');

        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();
        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        $wish = $wishlist->Wishes()->filter('Hash', $wishHash)->first();
        if (!$wish) {
            return $this->jsonError('Wish nicht gefunden', 404);
        }

        try {
            $data = json_decode($request->getBody(), true);

            if (isset($data['title'])) $wish->Title = $data['title'];
            if (isset($data['description'])) $wish->Description = $data['description'];
            if (isset($data['price'])) $wish->Price = $data['price'];
            if (isset($data['url'])) $wish->URL = $data['url'];

            $wish->write();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Wish aktualisiert',
                'data' => $wish->toArray()
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Fehler beim Aktualisieren des Wishes', 500);
        }
    }

    /**
     * DELETE /api/wishlist/wishlists/{hash}/wishes/{wishHash} - Wish löschen
     */
    public function delete_wish(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        $wishHash = $request->param('WishHash');

        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();
        if (!$wishlist) {
            return $this->jsonError('Wishlist nicht gefunden', 404);
        }

        $wish = $wishlist->Wishes()->filter('Hash', $wishHash)->first();
        if (!$wish) {
            return $this->jsonError('Wish nicht gefunden', 404);
        }

        try {
            $wish->delete();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Wish gelöscht'
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Fehler beim Löschen des Wishes', 500);
        }
    }

    /**
     * Hilfsmethode für JSON-Responses
     */
    protected function jsonResponse($data, $code = 200)
    {
        $response = HTTPResponse::create(json_encode($data, JSON_PRETTY_PRINT), $code);
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Hilfsmethode für JSON-Fehler
     */
    protected function jsonError($message, $code = 400)
    {
        return $this->jsonResponse([
            'success' => false,
            'error' => $message
        ], $code);
    }

    /**
     * Hash-Generator
     */
    protected function generateHash()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Link-Methode für URL-Generierung
     */
    public function Link($action = null)
    {
        return Controller::join_links('api/wishlist', $action);
    }
}
