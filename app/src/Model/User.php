<?php

namespace App\Model;

use App\Service\AvatarService;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;

/**
 * Eigener Wishling-Account (bewusst kein Silverstripe Member).
 * Login erfolgt ausschließlich per Magic Link, daher gibt es kein Passwort.
 */
class User extends DataObject
{
    private static $table_name = 'WishlingUser';

    private static $singular_name = 'Benutzer';

    private static $plural_name = 'Benutzer';

    private static $db = [
        'Email' => 'Varchar(254)',
        'DisplayName' => 'Varchar(100)',
        'LastLogin' => 'Datetime',
    ];

    private static $has_one = [
        // Genau ein Profilbild pro User, liegt in "Profilbilder/{ID}" (siehe AvatarService)
        'Avatar' => Image::class,
    ];

    private static $has_many = [
        'Wishlists' => Wishlist::class . '.Owner',
        'Sessions' => UserSession::class . '.User',
    ];

    private static $cascade_deletes = [
        'Sessions',
    ];

    private static $indexes = [
        'Email' => [
            'type' => 'unique',
            'columns' => ['Email'],
        ],
    ];

    private static $summary_fields = [
        'Email' => 'E-Mail',
        'DisplayName' => 'Name',
        'LastLogin.Nice' => 'Letzter Login',
    ];

    private static $searchable_fields = [
        'Email',
        'DisplayName',
    ];

    private static $default_sort = 'Email ASC';

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public static function isValidEmail(string $email): bool
    {
        return mb_strlen($email) <= 254 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function onBeforeDelete()
    {
        parent::onBeforeDelete();
        AvatarService::singleton()->remove($this, false);
    }

    public function getTitle(): string
    {
        return $this->DisplayName ?: (string) $this->Email;
    }

    public function toApi(): array
    {
        return [
            'email' => $this->Email,
            'displayName' => $this->DisplayName ?: '',
            'avatarUrl' => AvatarService::singleton()->url($this),
        ];
    }
}
