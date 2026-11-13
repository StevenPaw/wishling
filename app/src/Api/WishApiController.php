<?php

namespace App\Api;

use App\Model\Wish;
use SilverStripe\Control\HTTPResponse;

/**
 * /api/wishes – nur für den Besitzer der zugehörigen Liste
 *
 *   PUT  /$Hash           Wunsch aktualisieren { title?, type?, priority?, hidden?, description?, price?, url? }
 *   POST /$Hash/archive   Wunsch archivieren
 *   POST /$Hash/restore   Wunsch wiederherstellen
 */
class WishApiController extends ApiController
{
    use WishFieldsTrait;

    private static $url_handlers = [
        'POST $Hash!/archive' => 'archiveWish',
        'POST $Hash!/restore' => 'restoreWish',
        'PUT $Hash!' => 'updateWish',
    ];

    private static $allowed_actions = [
        'updateWish',
        'archiveWish',
        'restoreWish',
    ];

    public function updateWish(): HTTPResponse
    {
        $wish = $this->ownedWish();
        $this->applyWishFields($wish, false);
        $wish->write();

        return $this->json(['wish' => $wish->toApi(false)]);
    }

    public function archiveWish(): HTTPResponse
    {
        $wish = $this->ownedWish();
        $wish->Archived = true;
        $wish->write();

        return $this->json(['wish' => $wish->toApi(false)]);
    }

    public function restoreWish(): HTTPResponse
    {
        $wish = $this->ownedWish();
        $wish->Archived = false;
        $wish->write();

        return $this->json(['wish' => $wish->toApi(false)]);
    }

    private function ownedWish(): Wish
    {
        $user = $this->requireUser();
        $hash = (string) $this->getRequest()->param('Hash');

        /** @var Wish|null $wish */
        $wish = Wish::get()->filter('Hash', $hash)->first();
        if (!$wish || !$wish->Parent()->isOwnedBy($user)) {
            $this->fail('Wunsch nicht gefunden.', 404);
        }
        return $wish;
    }
}
