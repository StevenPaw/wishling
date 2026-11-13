<?php

namespace App\Api;

use App\Model\Wish;
use App\Model\Wishlist;
use App\Model\WishReservation;
use SilverStripe\Control\HTTPResponse;

/**
 * /api/public – Gast-Endpunkte, kein Login nötig. Zugriff nur über den Hash aus dem Link.
 *
 *   GET    /wishlists/$Hash            Liste in der Gast-Ansicht
 *   POST   /wishes/$Hash/reservation   Wunsch reservieren { guestName, message? }
 *   DELETE /wishes/$Hash/reservation   Reservierung aufheben (darf jeder mit dem Link)
 */
class PublicApiController extends ApiController
{
    private static $url_handlers = [
        'GET wishlists/$Hash!' => 'showWishlist',
        'POST wishes/$Hash!/reservation' => 'reserve',
        'DELETE wishes/$Hash!/reservation' => 'unreserve',
    ];

    private static $allowed_actions = [
        'showWishlist',
        'reserve',
        'unreserve',
    ];

    public function showWishlist(): HTTPResponse
    {
        $hash = (string) $this->getRequest()->param('Hash');

        /** @var Wishlist|null $wishlist */
        $wishlist = Wishlist::get()->filter(['Hash' => $hash, 'Archived' => false])->first();
        if (!$wishlist) {
            $this->fail('Wunschliste nicht gefunden.', 404);
        }

        // Besitzer sehen keine Reservierungen – die Überraschung soll erhalten bleiben
        $isOwner = $wishlist->isOwnedBy($this->currentUser());

        return $this->json([
            'wishlist' => $wishlist->toApiForGuest(!$isOwner),
            'isOwner' => $isOwner,
        ]);
    }

    public function reserve(): HTTPResponse
    {
        $wish = $this->publicWish();

        $guestName = (string) $this->stringField('guestName', 100, 'Der Name');
        if ($guestName === '') {
            $this->fail('Bitte gib deinen Namen ein.', 400);
        }
        $message = (string) $this->stringField('message', 2000, 'Die Nachricht');

        if ($wish->Reservations()->exists()) {
            $this->fail('Dieser Wunsch wurde bereits reserviert.', 409);
        }

        $reservation = WishReservation::create();
        $reservation->ParentID = $wish->ID;
        $reservation->GuestName = $guestName;
        $reservation->Message = $message;
        $reservation->write();

        return $this->json(['wish' => $wish->toApi(true)], 201);
    }

    public function unreserve(): HTTPResponse
    {
        $wish = $this->publicWish();

        foreach ($wish->Reservations() as $reservation) {
            $reservation->delete();
        }

        return $this->json(['wish' => $wish->toApi(true)]);
    }

    private function publicWish(): Wish
    {
        $hash = (string) $this->getRequest()->param('Hash');

        /** @var Wish|null $wish */
        $wish = Wish::get()->filter(['Hash' => $hash, 'Archived' => false, 'Hidden' => false])->first();
        if (!$wish || !$wish->Parent()->exists() || $wish->Parent()->Archived) {
            $this->fail('Wunsch nicht gefunden.', 404);
        }

        // Besitzer dürfen ihre eigenen Wünsche nicht reservieren
        if ($wish->Parent()->isOwnedBy($this->currentUser())) {
            $this->fail('Das ist deine eigene Wunschliste.', 403);
        }

        return $wish;
    }
}
