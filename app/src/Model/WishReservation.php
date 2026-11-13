<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

/**
 * Reservierung eines Wunsches durch einen Gast.
 * Jeder Gast mit dem Link darf Reservierungen anlegen und wieder aufheben (gewollt).
 */
class WishReservation extends DataObject
{
    private static $table_name = 'WishReservation';

    private static $singular_name = 'Reservierung';

    private static $plural_name = 'Reservierungen';

    private static $db = [
        'GuestName' => 'Varchar(100)',
        'Message' => 'Text',
    ];

    private static $has_one = [
        'Parent' => Wish::class,
    ];

    private static $summary_fields = [
        'Parent.Title' => 'Wunsch',
        'GuestName' => 'Gastname',
        'Message' => 'Nachricht',
    ];

    private static $searchable_fields = [
        'GuestName',
        'Message',
    ];

    private static $default_sort = 'Created ASC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('ParentID');
        return $fields;
    }
}
