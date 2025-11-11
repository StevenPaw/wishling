<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;

class Wishlist extends DataObject
{
    private static $table_name = 'Wishlist';

    private static $db = [
        'Title' => 'Varchar(255)',
        'Username' => 'Varchar(150)',
        'Description' => 'Text',
        'Active' => 'Boolean',
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
}
