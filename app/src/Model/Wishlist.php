<?php

namespace App\Model;

use App\Service\AvatarService;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataObject;

class Wishlist extends DataObject
{
    private static $table_name = 'Wishlist';

    private static $singular_name = 'Wunschliste';

    private static $plural_name = 'Wunschlisten';

    /**
     * Erlaubte Werte für DefaultSort (leer = Reihenfolge des Anlegens)
     */
    public const SORT_OPTIONS = ['', 'title-asc', 'title-desc', 'type-asc', 'type-desc', 'priority-desc', 'priority-asc', 'price-asc', 'price-desc', 'created-desc'];

    private static $db = [
        'Title' => 'Varchar(255)',
        'Description' => 'Text',
        'Hash' => 'Varchar(64)',
        'DefaultSort' => 'Varchar(50)',
        'Archived' => 'Boolean',
    ];

    private static $has_one = [
        'Owner' => User::class,
    ];

    private static $has_many = [
        'Wishes' => Wish::class . '.Parent',
    ];

    private static $owns = [
        'Wishes',
    ];

    private static $cascade_deletes = [
        'Wishes',
    ];

    private static $indexes = [
        'Hash' => [
            'type' => 'unique',
            'columns' => ['Hash'],
        ],
    ];

    private static $summary_fields = [
        'Title' => 'Titel',
        'Owner.Email' => 'Besitzer',
        'Wishes.Count' => 'Wünsche',
        'Archived.Nice' => 'Archiviert',
    ];

    private static $searchable_fields = [
        'Title',
        'Hash',
        'Archived',
    ];

    private static $default_sort = 'Created DESC';

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Hash) {
            $this->Hash = bin2hex(random_bytes(16));
        }
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user && $user->ID && (int) $this->OwnerID === (int) $user->ID;
    }

    /**
     * Öffentlicher Link für Gäste
     */
    public function ViewLink(): string
    {
        return Director::absoluteURL('/liste/' . $this->Hash);
    }

    /**
     * Kurzform für Übersichten (Meine Listen)
     */
    public function toApiSummary(): array
    {
        return [
            'hash' => $this->Hash,
            'title' => $this->Title,
            'description' => $this->Description ?: '',
            'wishCount' => $this->Wishes()->filter('Archived', false)->count(),
            'hiddenCount' => $this->Wishes()->filter(['Archived' => false, 'Hidden' => true])->count(),
            'archived' => (bool) $this->Archived,
            'shareUrl' => $this->ViewLink(),
            'lastEdited' => $this->LastEdited,
        ];
    }

    /**
     * Besitzer-Ansicht: inkl. archivierter Wünsche, ohne Reservierungen
     * (damit die Überraschung erhalten bleibt).
     */
    public function toApiForOwner(): array
    {
        $wishes = [];
        $archived = [];
        foreach ($this->Wishes() as $wish) {
            if ($wish->Archived) {
                $archived[] = $wish->toApi(false);
            } else {
                $wishes[] = $wish->toApi(false);
            }
        }

        return $this->baseApiData() + [
            'wishes' => $wishes,
            'archivedWishes' => $archived,
        ];
    }

    /**
     * Gast-Ansicht: nur aktive Wünsche, mit Reservierungsstatus
     */
    public function toApiForGuest(bool $includeReservations = true): array
    {
        $wishes = [];
        // Ausgeblendete Wünsche sehen Gäste nicht
        foreach ($this->Wishes()->filter(['Archived' => false, 'Hidden' => false]) as $wish) {
            $wishes[] = $wish->toApi($includeReservations);
        }

        return $this->baseApiData() + [
            'wishes' => $wishes,
        ];
    }

    private function baseApiData(): array
    {
        $owner = $this->Owner();

        return [
            'hash' => $this->Hash,
            'title' => $this->Title,
            'description' => $this->Description ?: '',
            'ownerName' => $owner && $owner->exists() ? $owner->DisplayName : '',
            'ownerAvatarUrl' => $owner && $owner->exists() ? AvatarService::singleton()->url($owner) : null,
            'defaultSort' => $this->DefaultSort ?: '',
            'shareUrl' => $this->ViewLink(),
            'created' => $this->Created,
            'lastEdited' => $this->LastEdited,
        ];
    }
}
