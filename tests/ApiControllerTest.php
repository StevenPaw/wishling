<?php

namespace App\Tests\Control;

use App\Control\ApiController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;

/**
 * Test für ApiController
 */
class ApiControllerTest extends SapphireTest
{
    protected $usesDatabase = false;

    public function testIndexReturnsJsonResponse()
    {
        $controller = new ApiController();
        $request = new HTTPRequest('GET', '/api/');
        $request->setSession(new Session([]));
        
        $response = $controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('version', $data);
        $this->assertEquals('API Endpoint', $data['message']);
        $this->assertEquals('1.0', $data['version']);
    }

    public function testGetDataWithId()
    {
        $controller = new ApiController();
        $request = new HTTPRequest('GET', '/api/data/123');
        $request->setSession(new Session([]));
        $request->setRouteParams(['ID' => '123']);
        
        $response = $controller->getData($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('123', $data['id']);
        $this->assertEquals('Beispieldaten', $data['data']);
    }

    public function testLinkGeneration()
    {
        $controller = new ApiController();
        
        $this->assertEquals('api/', $controller->Link());
        $this->assertEquals('api/test', $controller->Link('test'));
    }
}
