<?php

namespace App\Admin;

use App\Model\ExampleDataObject;
use App\Model\Wishlist;
use SilverStripe\Admin\ModelAdmin;

/**
 * ModelAdmin für die Verwaltung von DataObjects im Backend
 *
 * Dieser Admin erscheint im Silverstripe Backend-Menü
 */
class ExampleAdmin extends ModelAdmin
{
    private static $managed_models = [
        Wishlist::class,
    ];

    private static $url_segment = 'wishlists';

    private static $menu_title = 'Wunschlisten';

    private static $menu_icon_class = 'font-icon-database';
}
