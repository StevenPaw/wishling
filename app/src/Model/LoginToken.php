<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

/**
 * Einmal-Token für den Magic-Link-Login.
 * Gespeichert wird nur der SHA-256-Hash, nie das Token selbst.
 */
class LoginToken extends DataObject
{
    private static $table_name = 'WishlingLoginToken';

    private static $db = [
        'Email' => 'Varchar(254)',
        'TokenHash' => 'Varchar(64)',
        'Expires' => 'Datetime',
        'UsedAt' => 'Datetime',
        'RequestIP' => 'Varchar(45)',
    ];

    private static $indexes = [
        'TokenHash' => [
            'type' => 'unique',
            'columns' => ['TokenHash'],
        ],
        'Email' => true,
        'RequestIP' => true,
    ];

    private static $default_sort = 'Created DESC';
}
