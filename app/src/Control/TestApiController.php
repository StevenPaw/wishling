<?php

namespace App\Control;

use App\Model\Wishlist;
use App\Model\Wish;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use Exception;

/**
 * Einfacher Test-API-Controller
 */
class TestApiController extends Controller
{
    private static $allowed_actions = [
        'index',
        'wishlists',
        'test',
    ];

    private static $url_handlers = [
        'wishlists' => 'wishlists',
        'test' => 'test',
        '' => 'index',
    ];

    protected function init()
    {
        parent::init();

        // CORS Headers für Frontend
        $this->getResponse()->addHeader('Access-Control-Allow-Origin', '*');
        $this->getResponse()->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->getResponse()->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    /**
     * API Test
     */
    public function index(HTTPRequest $request)
    {
        return $this->jsonResponse([
            'name' => 'Test API',
            'method' => $request->httpMethod(),
            'action' => $request->param('Action'),
            'url' => $request->getURL(),
        ]);
    }

    /**
     * Test-Route
     */
    public function test(HTTPRequest $request)
    {
        return $this->jsonResponse([
            'message' => 'Test erfolgreich',
        ]);
    }

    /**
     * Liste aller Wishlists
     */
    public function wishlists(HTTPRequest $request)
    {
        try {
            $wishlists = Wishlist::get();
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
            return $this->jsonError('Fehler beim Laden der Wishlists: ' . $e->getMessage(), 500);
        }
    }

    /**
     * JSON Response Helper
     */
    protected function jsonResponse($data, $code = 200)
    {
        $response = HTTPResponse::create(json_encode($data, JSON_PRETTY_PRINT), $code);
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * JSON Error Helper
     */
    protected function jsonError($message, $code = 400)
    {
        return $this->jsonResponse([
            'success' => false,
            'error' => $message
        ], $code);
    }
}
