<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

/**
 * Eingeloggte Sitzung eines Users. Der Browser hält das Token in einem
 * HttpOnly-Cookie, in der Datenbank liegt nur der SHA-256-Hash.
 */
class UserSession extends DataObject
{
    private static $table_name = 'WishlingUserSession';

    private static $db = [
        'TokenHash' => 'Varchar(64)',
        'Expires' => 'Datetime',
        'LastSeen' => 'Datetime',
        'UserAgent' => 'Varchar(255)',
    ];

    private static $has_one = [
        'User' => User::class,
    ];

    private static $indexes = [
        'TokenHash' => [
            'type' => 'unique',
            'columns' => ['TokenHash'],
        ],
    ];

    private static $summary_fields = [
        'Created.Nice' => 'Angemeldet seit',
        'LastSeen.Nice' => 'Zuletzt aktiv',
        'UserAgent' => 'Browser',
    ];

    private static $default_sort = 'LastSeen DESC';
}
