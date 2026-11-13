<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

/**
 * Geschenk, das ein Gast zusätzlich zur Wunschliste einträgt.
 * Nur für Gäste sichtbar, der Besitzer der Liste bekommt diese Einträge nie ausgeliefert.
 */
class GuestGift extends DataObject
{
    private static $table_name = 'WishlistGuestGift';

    private static $singular_name = 'Weiteres Geschenk';

    private static $plural_name = 'Weitere Geschenke';

    private static $db = [
        'Title' => 'Varchar(255)',
        'GuestName' => 'Varchar(100)',
        'Hash' => 'Varchar(32)',
    ];

    private static $has_one = [
        'Wishlist' => Wishlist::class,
    ];

    private static $indexes = [
        'Hash' => [
            'type' => 'unique',
            'columns' => ['Hash'],
        ],
    ];

    private static $summary_fields = [
        'Title' => 'Geschenk',
        'GuestName' => 'Von',
        'Created.Nice' => 'Eingetragen',
    ];

    private static $default_sort = 'Created ASC, ID ASC';

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Hash) {
            $this->Hash = bin2hex(random_bytes(16));
        }
    }

    public function toApi(): array
    {
        return [
            'hash' => $this->Hash,
            'title' => $this->Title,
            'guestName' => $this->GuestName,
            'created' => date(DATE_ATOM, strtotime((string) $this->Created)),
        ];
    }
}
