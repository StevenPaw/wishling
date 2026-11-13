<?php

namespace App\Api;

use App\Model\GuestGift;
use App\Model\Wish;
use App\Model\Wishlist;
use App\Model\WishReservation;
use App\Model\WishReservationEvent;
use SilverStripe\Control\HTTPResponse;

/**
 * /api/public – Gast-Endpunkte, kein Login nötig. Zugriff nur über den Hash aus dem Link.
 *
 *   GET    /wishlists/$Hash            Liste in der Gast-Ansicht
 *   POST   /wishes/$Hash/reservation   Wunsch reservieren         { guestName, message? }
 *   DELETE /wishes/$Hash/reservation   Reservierung freigeben     { guestName } (darf jeder mit dem Link)
 *   POST   /wishlists/$Hash/gifts      Weiteres Geschenk eintragen { guestName, title }
 *   DELETE /gifts/$Hash                Weiteres Geschenk entfernen (darf jeder mit dem Link)
 *
 * Reservierungen, Verlauf und weitere Geschenke bekommt nur, wer NICHT Besitzer der Liste ist.
 */
class PublicApiController extends ApiController
{
    private static $url_handlers = [
        'GET wishlists/$Hash!' => 'showWishlist',
        'POST wishlists/$Hash!/gifts' => 'addGift',
        'POST wishes/$Hash!/reservation' => 'reserve',
        'DELETE wishes/$Hash!/reservation' => 'unreserve',
        'DELETE gifts/$Hash!' => 'removeGift',
    ];

    private static $allowed_actions = [
        'showWishlist',
        'addGift',
        'reserve',
        'unreserve',
        'removeGift',
    ];

    public function showWishlist(): HTTPResponse
    {
        $wishlist = $this->publicWishlist((string) $this->getRequest()->param('Hash'));

        // Besitzer sehen weder Reservierungen noch Verlauf noch weitere Geschenke – die Überraschung bleibt
        $isOwner = $wishlist->isOwnedBy($this->currentUser());

        return $this->json([
            'wishlist' => $wishlist->toApiForGuest(!$isOwner),
            'isOwner' => $isOwner,
        ]);
    }

    public function reserve(): HTTPResponse
    {
        $wish = $this->publicWish();
        $guestName = $this->requiredGuestName();
        $message = (string) $this->stringField('message', 2000, 'Die Nachricht');

        if ($wish->Reservations()->exists()) {
            $this->fail('Dieser Wunsch wurde bereits reserviert.', 409);
        }

        $reservation = WishReservation::create();
        $reservation->ParentID = $wish->ID;
        $reservation->GuestName = $guestName;
        $reservation->Message = $message;
        $reservation->write();

        WishReservationEvent::log($wish, WishReservationEvent::ACTION_RESERVED, $guestName);

        return $this->json(['wish' => $wish->toApi(true)], 201);
    }

    public function unreserve(): HTTPResponse
    {
        $wish = $this->publicWish();
        $guestName = $this->requiredGuestName();

        if (!$wish->Reservations()->exists()) {
            $this->fail('Dieser Wunsch ist nicht reserviert.', 409);
        }

        foreach ($wish->Reservations() as $reservation) {
            $reservation->delete();
        }

        WishReservationEvent::log($wish, WishReservationEvent::ACTION_RELEASED, $guestName);

        return $this->json(['wish' => $wish->toApi(true)]);
    }

    public function addGift(): HTTPResponse
    {
        $wishlist = $this->publicWishlist((string) $this->getRequest()->param('Hash'));
        $this->forbidOwner($wishlist);

        $guestName = $this->requiredGuestName();
        $title = (string) $this->stringField('title', 255, 'Das Geschenk');
        if ($title === '') {
            $this->fail('Bitte gib ein, was du schenkst.', 400);
        }

        $gift = GuestGift::create();
        $gift->WishlistID = $wishlist->ID;
        $gift->Title = $title;
        $gift->GuestName = $guestName;
        $gift->write();

        return $this->json(['gift' => $gift->toApi()], 201);
    }

    public function removeGift(): HTTPResponse
    {
        $hash = (string) $this->getRequest()->param('Hash');

        /** @var GuestGift|null $gift */
        $gift = GuestGift::get()->filter('Hash', $hash)->first();
        if (!$gift) {
            $this->fail('Eintrag nicht gefunden.', 404);
        }
        $wishlist = $this->publicWishlist((string) $gift->Wishlist()->Hash);
        $this->forbidOwner($wishlist);

        $gift->delete();

        return $this->json(['success' => true]);
    }

    private function requiredGuestName(): string
    {
        $guestName = (string) $this->stringField('guestName', 100, 'Der Name');
        if ($guestName === '') {
            $this->fail('Bitte gib deinen Namen ein.', 400);
        }
        return $guestName;
    }

    private function publicWishlist(string $hash): Wishlist
    {
        /** @var Wishlist|null $wishlist */
        $wishlist = $hash !== ''
            ? Wishlist::get()->filter(['Hash' => $hash, 'Archived' => false])->first()
            : null;
        if (!$wishlist) {
            $this->fail('Wunschliste nicht gefunden.', 404);
        }
        return $wishlist;
    }

    private function forbidOwner(Wishlist $wishlist): void
    {
        if ($wishlist->isOwnedBy($this->currentUser())) {
            $this->fail('Das ist deine eigene Wunschliste.', 403);
        }
    }

    private function publicWish(): Wish
    {
        $hash = (string) $this->getRequest()->param('Hash');

        /** @var Wish|null $wish */
        $wish = Wish::get()->filter(['Hash' => $hash, 'Archived' => false, 'Hidden' => false])->first();
        if (!$wish || !$wish->Parent()->exists() || $wish->Parent()->Archived) {
            $this->fail('Wunsch nicht gefunden.', 404);
        }

        // Besitzer dürfen ihre eigenen Wünsche nicht reservieren oder freigeben
        $this->forbidOwner($wish->Parent());

        return $wish;
    }
}
