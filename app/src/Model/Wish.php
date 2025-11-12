<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

class Wish extends DataObject
{
    private static $table_name = 'Wish';

    private static $db = [
        'Title' => 'Varchar(255)',
        'Description' => 'Text',
        'Hash' => 'Varchar(64)',
        'Price' => 'Decimal(10,2)',
        'URL' => 'Varchar(255)',
    ];

    private static $has_one = [
        'Parent' => Wishlist::class,
    ];

    private static $summary_fields = [
        'Title' => 'Titel',
        'Description' => 'Beschreibung',
        'Price' => 'Preis',
        'URL' => 'Link',
    ];

    private static $searchable_fields = [
        'Title',
        'Price',
        'Hash',
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
            'title' => $this->Title,
            'description' => $this->Description,
            'price' => (float) $this->Price,
            'url' => $this->URL,
            'hash' => $this->Hash,
            'created' => $this->Created,
            'lastEdited' => $this->LastEdited,
            'parentId' => $this->ParentID,
            'parentHash' => $this->Parent()->Hash ?? null,
        ];
    }

    /**
     * Generiere Hash automatisch beim Erstellen
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Hash) {
            $this->Hash = bin2hex(random_bytes(16));
        }
    }
}
