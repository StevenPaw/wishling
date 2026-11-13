<?php

namespace App\Controllers;

use App\Model\Wishlist;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * Liefert für alle Frontend-Routen dasselbe HTML-Grundgerüst aus.
 * Das eigentliche Routing übernimmt der Vue Router im Browser.
 *
 * Für geteilte Listen (/liste/{hash}) werden Titel und OpenGraph-Tags
 * serverseitig gesetzt, damit Link-Vorschauen (z. B. in Messengern) passen.
 */
class SpaController extends Controller
{
    private static $url_handlers = [
        '$@' => 'index',
    ];

    private static $allowed_actions = [
        'index',
    ];

    public function index(HTTPRequest $request): HTTPResponse
    {
        $title = 'Wishling';
        $description = 'Wunschlisten erstellen, teilen und Geschenke reservieren.';

        $wishlist = $this->sharedWishlist($request);
        if ($wishlist) {
            $title = $wishlist->Title . ' – Wishling';
            $description = $wishlist->Description ?: 'Schau dir meine Wunschliste an!';
        }

        $body = $this->customise([
            'Title' => $title,
            'MetaDescription' => $description,
            'CanonicalURL' => Director::absoluteURL($request->getURL()),
        ])->renderWith('App\\Controllers\\SpaController');

        $response = HTTPResponse::create($body);
        $response->addHeader('Content-Type', 'text/html; charset=utf-8');
        $response->addHeader('X-Frame-Options', 'DENY');
        $response->addHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        return $response;
    }

    private function sharedWishlist(HTTPRequest $request): ?Wishlist
    {
        if (!preg_match('#^liste/([a-f0-9]{16,64})$#', $request->getURL(), $matches)) {
            return null;
        }
        return Wishlist::get()->filter(['Hash' => $matches[1], 'Archived' => false])->first();
    }
}
