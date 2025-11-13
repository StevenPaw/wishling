<?php

namespace App\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

class ApiController extends Controller
{
    /**
     * Erlaubte Actions
     */
    private static $allowed_actions = [
        'index',
        'getData',
    ];

    /**
     * URL-Handlers für diese Controller-Klasse
     */
    private static $url_handlers = [
        'data/$ID' => 'getData',
        '' => 'index',
    ];

    /**
     * Index Action
     */
    public function index(HTTPRequest $request)
    {
        return $this->jsonResponse([
            'message' => 'API Endpoint',
            'version' => '1.0',
        ]);
    }

    /**
     * Beispiel für eine Action mit Parameter
     */
    public function getData(HTTPRequest $request)
    {
        $id = $request->param('ID');

        // Hier könntest du z.B. DataObjects abfragen
        // $object = MyDataObject::get()->byID($id);

        return $this->jsonResponse([
            'id' => $id,
            'data' => 'Beispieldaten',
        ]);
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
     * Link-Methode für URL-Generierung
     */
    public function Link($action = null)
    {
        return Controller::join_links('api', $action);
    }
}
