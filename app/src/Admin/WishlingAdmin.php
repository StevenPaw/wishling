<?php

namespace App\Admin;

use App\Model\User;
use App\Model\Wishlist;
use SilverStripe\Admin\ModelAdmin;

/**
 * Backend-Verwaltung für Wishling-Daten (für Admins, unabhängig von den Wishling-Usern)
 */
class WishlingAdmin extends ModelAdmin
{
    private static $managed_models = [
        Wishlist::class,
        User::class,
    ];

    private static $url_segment = 'wishling';

    private static $menu_title = 'Wishling';

    private static $menu_icon_class = 'font-icon-database';
}
