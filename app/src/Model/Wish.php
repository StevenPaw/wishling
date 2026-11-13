<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

class Wish extends DataObject
{
    private static $table_name = 'Wish';

    private static $singular_name = 'Wunsch';

    private static $plural_name = 'Wünsche';

    public const PRIORITY_MIN = 0;

    public const PRIORITY_MAX = 5;

    private static $db = [
        'Title' => 'Varchar(255)',
        // Freitext-Kategorie, z. B. "Videospiel", "T-Shirt", "Brettspiel"
        'Type' => 'Varchar(100)',
        'Description' => 'Text',
        'Hash' => 'Varchar(64)',
        // Bewusst Text: erlaubt auch Preisspannen oder Freitext ("ca. 20–30 €", "egal")
        'Price' => 'Varchar(100)',
        'URL' => 'Varchar(2048)',
        'Archived' => 'Boolean',
        // Für Gäste ausgeblendet (z. B. schon anderweitig geschenkt), bleibt für den Besitzer sichtbar
        'Hidden' => 'Boolean',
        // Stärke des Wunsches: 0 (keine Angabe) bis 5
        'Priority' => 'Int',
    ];

    private static $has_one = [
        'Parent' => Wishlist::class,
    ];

    private static $has_many = [
        'Reservations' => WishReservation::class . '.Parent',
    ];

    private static $cascade_deletes = [
        'Reservations',
    ];

    private static $owns = [
        'Reservations',
    ];

    private static $indexes = [
        'Hash' => [
            'type' => 'unique',
            'columns' => ['Hash'],
        ],
    ];

    private static $summary_fields = [
        'Title' => 'Titel',
        'Priority' => 'Stärke',
        'Type' => 'Typ',
        'Price' => 'Preis',
        'URL' => 'Link',
        'Hidden.Nice' => 'Ausgeblendet',
        'Archived.Nice' => 'Archiviert',
    ];

    private static $searchable_fields = [
        'Title',
        'Hash',
        'Archived',
    ];

    private static $default_sort = 'Created ASC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('ParentID');
        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Hash) {
            $this->Hash = bin2hex(random_bytes(16));
        }
    }

    /**
     * Normalisiert eine Link-Eingabe. Erlaubt nur http(s), ergänzt fehlendes Schema.
     * Gibt null zurück, wenn der Link ungültig ist.
     */
    public static function normalizeUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^[a-z][a-z0-9+.\-]*://#i', $url)) {
            $url = 'https://' . $url;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false || mb_strlen($url) > 2048) {
            return null;
        }
        return $url;
    }

    public function getActiveReservation(): ?WishReservation
    {
        return $this->Reservations()->first();
    }

    public function toApi(bool $includeReservation): array
    {
        $data = [
            'hash' => $this->Hash,
            'title' => $this->Title,
            'type' => $this->Type ?: '',
            'priority' => (int) $this->Priority,
            'description' => $this->Description ?: '',
            'price' => $this->Price ?: '',
            'url' => $this->URL ?: '',
            'archived' => (bool) $this->Archived,
            'hidden' => (bool) $this->Hidden,
            'created' => $this->Created,
        ];

        if ($includeReservation) {
            $reservation = $this->getActiveReservation();
            $data['reservation'] = $reservation ? [
                'guestName' => $reservation->GuestName,
                'created' => $reservation->Created,
            ] : null;
        }

        return $data;
    }
}
