<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

class WishReservation extends DataObject
{
    private static $table_name = 'WishReservation';

    private static $db = [
        'GuestName' => 'Varchar(255)',
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
        'Parent.Title',
    ];

    private static $default_sort = 'Created ASC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('ParentID');

        return $fields;
    }

    /**
     * JSON-Serialisierung für API
     */
    public function toArray()
    {
        return [
            'id' => $this->ID,
            'guestName' => $this->GuestName,
            'message' => $this->Message,
            'wishTitle' => $this->Parent()->Title,
        ];
    }

    /**
     * Generiere Hash automatisch beim Erstellen
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
    }
}
