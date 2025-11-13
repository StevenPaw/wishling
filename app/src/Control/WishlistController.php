<?php

namespace App\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\View\Requirements;
use App\Model\Wishlist;
use App\Model\Wish;

/**
 * Frontend Controller für Wishlist-Management
 * Bietet sowohl Owner- als auch Guest-Views
 */
class WishlistController extends Controller
{
    private static $url_handlers = [
        '' => 'index',
        'create' => 'createWishlist',
        'list/$Hash' => 'ownerView', 
        'view/$Hash' => 'guestView',
        '$Hash/edit' => 'ownerView',
        '$Hash/share' => 'shareView'
    ];

    private static $allowed_actions = [
        'index',
        'createWishlist', 
        'ownerView',
        'guestView',
        'shareView'
    ];

    /**
     * Hauptseite - Neue Wishlist erstellen
     */
    public function index(HTTPRequest $request)
    {
        // Requirements::css('app/css/wishlist.css');
        // Requirements::javascript('app/js/wishlist.js');
        
        return $this->customise([
            'Title' => 'Wishling - Deine Wunschliste erstellen',
            'MetaTitle' => 'Wishling - Erstelle deine persönliche Wunschliste'
        ])->renderWith(['WishlistIndex', 'Page']);
    }

    /**
     * Owner-Ansicht - Vollzugriff auf Wishlist
     */
    public function ownerView(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        if (!$hash) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();
        if (!$wishlist) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        // Requirements::css('app/css/wishlist.css');
        // Requirements::javascript('app/js/wishlist.js');

        // Guest-URL für Teilen generieren
        $guestURL = $this->Link('view/' . $hash);
        $shareURL = $request->getScheme() . '://' . $request->getHost() . $guestURL;

        return $this->customise([
            'Title' => 'Meine Wunschliste - ' . $wishlist->Name,
            'MetaTitle' => $wishlist->Name . ' - Wunschliste verwalten',
            'Wishlist' => $wishlist,
            'ShareURL' => $shareURL,
            'IsOwner' => true,
            'CurrentHash' => $hash
        ])->renderWith(['WishlistOwner', 'Page']);
    }

    /**
     * Guest-Ansicht - Nur lesen, kein Bearbeiten
     */
    public function guestView(HTTPRequest $request) 
    {
        $hash = $request->param('Hash');
        if (!$hash) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();
        if (!$wishlist) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        // Requirements::css('app/css/wishlist.css');
        // Requirements::javascript('app/js/wishlist-guest.js');

        return $this->customise([
            'Title' => $wishlist->Name . ' - Wunschliste',
            'MetaTitle' => $wishlist->Name . ' - Wunschliste von ' . $wishlist->CreatorName,
            'Wishlist' => $wishlist,
            'IsOwner' => false,
            'CurrentHash' => $hash
        ])->renderWith(['WishlistGuest', 'Page']);
    }

    /**
     * Share-Info Seite
     */
    public function shareView(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        if (!$hash) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();
        if (!$wishlist) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        $ownerURL = $this->Link('list/' . $hash);
        $guestURL = $this->Link('view/' . $hash);
        $baseURL = $request->getScheme() . '://' . $request->getHost();

        return $this->customise([
            'Title' => 'Wunschliste teilen - ' . $wishlist->Name,
            'Wishlist' => $wishlist,
            'OwnerURL' => $baseURL . $ownerURL,
            'GuestURL' => $baseURL . $guestURL
        ])->renderWith(['WishlistShare', 'Page']);
    }

    /**
     * JSON-Response für AJAX-Calls
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        $response = $this->getResponse();
        $response->setStatusCode($statusCode);
        $response->addHeader('Content-Type', 'application/json');
        return json_encode($data);
    }
}
