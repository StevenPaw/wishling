<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Control\Director;
class Wishlist extends DataObject
{
    private static $table_name = 'Wishlist';

    private static $db = [
        'Title' => 'Varchar(255)',
        'Username' => 'Varchar(150)',
        'Description' => 'Text',
        'Active' => 'Boolean',
        'OwnerHash' => 'Varchar(64)',
        'Hash' => 'Varchar(64)',
    ];

    private static $has_many = [
        'Wishes' => Wish::class,
    ];

    private static $owns = [
        'Wishes',
    ];

    private static $summary_fields = [
        'Title' => 'Titel',
        'Description' => 'Beschreibung',
        'Active.Nice' => 'Aktiv',
        'Hash' => 'Hash',
    ];

    private static $searchable_fields = [
        'Title',
        'Active',
        'Hash',
    ];

    private static $default_sort = 'Created ASC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', GridField::create(
            'Wishes',
            'Wünsche',
            $this->Wishes(),
            GridFieldConfig_RelationEditor::create()
        ));

        return $fields;
    }

    /**
     * JSON-Serialisierung für API
     */
    public function toArray()
    {
        $wishes = [];
        foreach ($this->Wishes() as $wish) {
            $wishes[] = $wish->toArray();
        }

        return [
            'id' => $this->ID,
            'title' => $this->Title,
            'username' => $this->Username,
            'description' => $this->Description,
            'active' => (bool) $this->Active,
            'hash' => $this->Hash,
            'created' => $this->Created,
            'lastEdited' => $this->LastEdited,
            'wishes' => $wishes,
            'wishCount' => count($wishes)
        ];
    }

    /**
     * Generiere Hash automatisch beim Erstellen
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Hash) {
            $this->Hash = bin2hex(random_bytes(32));
        }

        if (!$this->OwnerHash) {
            $this->OwnerHash = bin2hex(random_bytes(32));
        }
    }

    /**
     * Gibt den Link zur Wunschliste zurück
     * @param bool $absolute Wenn true, mit Domain
     * @return string
     */
    public function ViewLink()
    {
        $link = '/wishlist/view/' . $this->Hash;
        return Director::absoluteURL($link);
    }
}
