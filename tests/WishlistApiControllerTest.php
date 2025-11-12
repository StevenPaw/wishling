<?php

namespace App\Tests\Control;

use App\Control\WishlistApiController;
use App\Model\Wishlist;
use App\Model\Wish;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Control\Session;
use SilverStripe\ORM\DataObject;

/**
 * Test für WishlistApiController
 */
class WishlistApiControllerTest extends SapphireTest
{
    protected $usesDatabase = true;
    
    protected static $fixture_file = 'WishlistApiControllerTest.yml';

    protected static $extra_dataobjects = [
        Wishlist::class,
        Wish::class,
    ];

    public function testIndexReturnsJsonResponse()
    {
        $controller = new WishlistApiController();
        $request = new HTTPRequest('GET', '/api/wishlist/');
        $request->setSession(new Session([]));
        
        $response = $controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('Wishlist API', $data['name']);
    }

    public function testCreateWishlist()
    {
        $controller = new WishlistApiController();
        $request = new HTTPRequest('POST', '/api/wishlist/wishlists');
        $request->setSession(new Session([]));
        
        // JSON Body simulieren
        $postData = [
            'name' => 'Test Wishlist',
            'description' => 'Test Description'
        ];
        
        // Request Body setzen
        $request->setBody(json_encode($postData));
        $request->addHeader('Content-Type', 'application/json');
        
        $response = $controller->wishlists($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('wishlist', $data);
        $this->assertEquals('Test Wishlist', $data['wishlist']['name']);
    }

    public function testGetWishlists()
    {
        // Erstelle eine Test-Wishlist
        $wishlist = new Wishlist();
        $wishlist->Name = 'Test Wishlist';
        $wishlist->Description = 'Test Description';
        $wishlist->write();

        $controller = new WishlistApiController();
        $request = new HTTPRequest('GET', '/api/wishlist/wishlists');
        $request->setSession(new Session([]));
        
        $response = $controller->wishlists($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('wishlists', $data);
        $this->assertGreaterThan(0, count($data['wishlists']));
        
        // Prüfe ob unsere Test-Wishlist dabei ist
        $found = false;
        foreach ($data['wishlists'] as $wl) {
            if ($wl['name'] === 'Test Wishlist') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test Wishlist should be found in results');
    }

    public function testCorsHeaders()
    {
        $controller = new WishlistApiController();
        $request = new HTTPRequest('GET', '/api/wishlist/');
        $request->setSession(new Session([]));
        
        $response = $controller->index($request);
        
        $this->assertEquals('*', $response->getHeader('Access-Control-Allow-Origin'));
        $this->assertEquals('GET, POST, PUT, DELETE, OPTIONS', $response->getHeader('Access-Control-Allow-Methods'));
        $this->assertEquals('Content-Type, Authorization', $response->getHeader('Access-Control-Allow-Headers'));
    }
}
