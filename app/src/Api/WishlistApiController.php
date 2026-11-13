<?php

namespace App\Api;

use App\Model\Wish;
use App\Model\Wishlist;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\DB;

/**
 * /api/wishlists – nur für eingeloggte Besitzer
 *
 *   GET  /                  eigene Listen
 *   POST /                  Liste anlegen        { title, description? }
 *   GET  /$Hash             Liste (Besitzer-Ansicht)
 *   PUT  /$Hash             Liste aktualisieren  { title?, description?, defaultSort? }
 *   POST /$Hash/archive     Liste archivieren
 *   POST /$Hash/restore     Liste wiederherstellen
 *   POST /$Hash/wishes      Wunsch hinzufügen    { title, type?, priority?, hidden?, description?, price?, url? }
 *   POST /$Hash/duplicate   Liste duplizieren    { title?, description? }
 */
class WishlistApiController extends ApiController
{
    use WishFieldsTrait;

    private static $url_handlers = [
        'POST $Hash!/wishes' => 'addWish',
        'POST $Hash!/duplicate' => 'duplicateWishlist',
        'POST $Hash!/archive' => 'archiveWishlist',
        'POST $Hash!/restore' => 'restoreWishlist',
        'GET $Hash!' => 'showWishlist',
        'PUT $Hash!' => 'updateWishlist',
        'GET ' => 'listWishlists',
        'POST ' => 'createWishlist',
    ];

    private static $allowed_actions = [
        'listWishlists',
        'createWishlist',
        'showWishlist',
        'updateWishlist',
        'archiveWishlist',
        'restoreWishlist',
        'addWish',
        'duplicateWishlist',
    ];

    public function listWishlists(): HTTPResponse
    {
        $user = $this->requireUser();

        $lists = [];
        foreach ($user->Wishlists()->sort('Created', 'DESC') as $wishlist) {
            $lists[] = $wishlist->toApiSummary();
        }

        return $this->json(['wishlists' => $lists]);
    }

    public function createWishlist(): HTTPResponse
    {
        $user = $this->requireUser();

        $title = (string) $this->stringField('title', 255, 'Der Titel');
        if ($title === '') {
            $this->fail('Bitte gib einen Titel für die Wunschliste ein.', 400);
        }

        $wishlist = Wishlist::create();
        $wishlist->Title = $title;
        $wishlist->Description = (string) $this->stringField('description', 5000, 'Die Beschreibung');
        $wishlist->OwnerID = $user->ID;
        $wishlist->write();

        return $this->json(['wishlist' => $wishlist->toApiForOwner()], 201);
    }

    public function showWishlist(): HTTPResponse
    {
        return $this->json(['wishlist' => $this->ownedWishlist()->toApiForOwner()]);
    }

    public function updateWishlist(): HTTPResponse
    {
        $wishlist = $this->ownedWishlist();

        $title = $this->stringField('title', 255, 'Der Titel');
        if ($title !== null) {
            if ($title === '') {
                $this->fail('Der Titel darf nicht leer sein.', 400);
            }
            $wishlist->Title = $title;
        }

        $description = $this->stringField('description', 5000, 'Die Beschreibung');
        if ($description !== null) {
            $wishlist->Description = $description;
        }

        $defaultSort = $this->stringField('defaultSort', 50, 'Die Sortierung');
        if ($defaultSort !== null) {
            if (!in_array($defaultSort, Wishlist::SORT_OPTIONS, true)) {
                $this->fail('Unbekannte Sortierung.', 400);
            }
            $wishlist->DefaultSort = $defaultSort;
        }

        $wishlist->write();

        return $this->json(['wishlist' => $wishlist->toApiForOwner()]);
    }

    public function archiveWishlist(): HTTPResponse
    {
        $wishlist = $this->ownedWishlist();
        $wishlist->Archived = true;
        $wishlist->write();

        return $this->json(['wishlist' => $wishlist->toApiSummary()]);
    }

    public function restoreWishlist(): HTTPResponse
    {
        $wishlist = $this->ownedWishlist();
        $wishlist->Archived = false;
        $wishlist->write();

        return $this->json(['wishlist' => $wishlist->toApiSummary()]);
    }

    public function addWish(): HTTPResponse
    {
        $wishlist = $this->ownedWishlist();

        $wish = Wish::create();
        $wish->ParentID = $wishlist->ID;
        $this->applyWishFields($wish, true);
        $wish->write();

        return $this->json(['wish' => $wish->toApi(false)], 201);
    }

    /**
     * Legt eine Kopie der Liste an (z. B. die Liste vom letzten Jahr).
     * Übernommen werden nur aktive Wünsche – ohne Reservierungen und archivierte Wünsche.
     */
    public function duplicateWishlist(): HTTPResponse
    {
        $source = $this->ownedWishlist();

        $title = $this->stringField('title', 255, 'Der Titel');
        if ($title === null || $title === '') {
            $title = $source->Title . ' (Kopie)';
        }
        $description = $this->stringField('description', 5000, 'Die Beschreibung');

        $copy = Wishlist::create();
        $copy->Title = mb_substr($title, 0, 255);
        $copy->Description = $description ?? $source->Description;
        $copy->DefaultSort = $source->DefaultSort;
        $copy->OwnerID = $source->OwnerID;

        DB::get_conn()->withTransaction(function () use ($source, $copy) {
            $copy->write();
            foreach ($source->Wishes()->filter('Archived', false) as $wish) {
                $newWish = Wish::create();
                $newWish->ParentID = $copy->ID;
                $newWish->Title = $wish->Title;
                $newWish->Type = $wish->Type;
                $newWish->Priority = $wish->Priority;
                $newWish->Hidden = $wish->Hidden;
                $newWish->Description = $wish->Description;
                $newWish->Price = $wish->Price;
                $newWish->URL = $wish->URL;
                $newWish->write();
            }
        });

        return $this->json(['wishlist' => $copy->toApiForOwner()], 201);
    }

    /**
     * Liste zum Hash, die dem eingeloggten User gehört – sonst 404
     * (bewusst kein 403, um die Existenz fremder Listen nicht preiszugeben).
     */
    private function ownedWishlist(): Wishlist
    {
        $user = $this->requireUser();
        $hash = (string) $this->getRequest()->param('Hash');

        /** @var Wishlist|null $wishlist */
        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();
        if (!$wishlist || !$wishlist->isOwnedBy($user)) {
            $this->fail('Wunschliste nicht gefunden.', 404);
        }
        return $wishlist;
    }
}
