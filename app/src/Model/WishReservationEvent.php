<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

/**
 * Verlaufseintrag zu einer Reservierung (reserviert / freigegeben).
 * Nur für Gäste sichtbar, der Besitzer der Liste bekommt den Verlauf nie ausgeliefert.
 */
class WishReservationEvent extends DataObject
{
    public const ACTION_RESERVED = 'reserved';

    public const ACTION_RELEASED = 'released';

    private static $table_name = 'WishReservationEvent';

    private static $singular_name = 'Reservierungsverlauf';

    private static $plural_name = 'Reservierungsverlauf';

    private static $db = [
        'Action' => "Enum('reserved,released','reserved')",
        'GuestName' => 'Varchar(100)',
    ];

    private static $has_one = [
        'Wish' => Wish::class,
    ];

    private static $summary_fields = [
        'Created.Nice' => 'Zeitpunkt',
        'Action' => 'Aktion',
        'GuestName' => 'Name',
    ];

    private static $default_sort = 'Created DESC, ID DESC';

    public static function log(Wish $wish, string $action, string $guestName): void
    {
        $event = self::create();
        $event->WishID = $wish->ID;
        $event->Action = $action;
        $event->GuestName = $guestName;
        $event->write();
    }

    public function toApi(): array
    {
        return [
            'action' => $this->Action,
            'guestName' => $this->GuestName,
            'created' => date(DATE_ATOM, strtotime((string) $this->Created)),
        ];
    }
}
