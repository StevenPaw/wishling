<?php

namespace App\Controllers;

use App\Model\Wish;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use App\Model\Wishlist;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;

/**
 * Frontend Controller für Wishlist-Management
 * Bietet sowohl Owner- als auch Guest-Views
 */
class WishlistController extends Controller
{
    private static $url_segment = 'wishlist';

    private static $allowed_actions = [
        'index',
        'createWishlist',
        'edit',
        'view',
        'CreateWishlistForm',
        'doCreateWishlist',
        'EditWishlistForm',
        'doEditWishlist',
        'AddWishForm',
        'doAddWish'
    ];

    /**
     * Owner-Ansicht - Vollzugriff auf Wishlist
     */
    public function edit(HTTPRequest $request)
    {
        $hash = $request->param('ID');
        if (!$hash) {
            return $this->httpError(404, 'Hash fehlt');
        }

        $wishlist = Wishlist::get()->filter('OwnerHash', $hash)->first();
        if (!$wishlist) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        $wishes = $wishlist->Wishes()->filter('Archived', false);
        $archivedWishes = $wishlist->Wishes()->filter('Archived', true);

        return $this->customise([
            'Wishlist' => $wishlist,
            'Wishes' => $wishes,
            'ArchivedWishes' => $archivedWishes,
            'ViewLink' => $wishlist->ViewLink()
        ])->renderWith(['App\Controllers\WishlistController_edit', 'Page']);
    }

    /**
     * Guest-Ansicht - Nur lesen, kein Bearbeiten
     */
    public function view(HTTPRequest $request)
    {
        $hash = $request->param('Hash');
        if (!$hash) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        $wishlist = Wishlist::get()->filter('Hash', $hash)->first();
        if (!$wishlist) {
            return $this->httpError(404, 'Wishlist nicht gefunden');
        }

        // Requirements::css('app/css/wishlist.css');
        // Requirements::javascript('app/js/wishlist-guest.js');

        return $this->customise([
            'Title' => $wishlist->Name . ' - Wunschliste',
            'MetaTitle' => $wishlist->Name . ' - Wunschliste von ' . $wishlist->CreatorName,
            'Wishlist' => $wishlist,
            'IsOwner' => false,
            'CurrentHash' => $hash
        ])->renderWith(['WishlistGuest', 'Page']);
    }

    /**
     * JSON-Response für AJAX-Calls
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        $response = $this->getResponse();
        $response->setStatusCode($statusCode);
        $response->addHeader('Content-Type', 'application/json');
        return json_encode($data);
    }

    public function CreateWishlistForm()
    {
        $fields = FieldList::create(
            TextField::create('Title', 'Name der Wunschliste'),
            TextField::create('Username', 'Dein Name'),
            TextField::create('Description', 'Beschreibung deiner Wunschliste')
        );
        $actions = FieldList::create(
            FormAction::create('doCreateWishlist', 'Wunschliste anlegen')
        );
        $validator = RequiredFieldsValidator::create('Title', 'Username');
        return Form::create($this, 'CreateWishlistForm', $fields, $actions, $validator);
    }

    public function EditWishlistForm()
    {
        $fields = FieldList::create(
            TextField::create('Title', 'Name der Wunschliste'),
            TextField::create('Username', 'Dein Name'),
            TextField::create('Description', 'Beschreibung deiner Wunschliste'),
            HiddenField::create('OwnerHash', 'OwnerHash', null)
        );
        $actions = FieldList::create(
            FormAction::create('doEditWishlist', 'Wunschliste speichern')
        );
        $validator = RequiredFieldsValidator::create('Title', 'Username');
        $form = Form::create($this, 'EditWishlistForm', $fields, $actions, $validator);
        return $form;
    }

    public function doCreateWishlist($data, $form)
    {
        $wishlist = Wishlist::create();
        $form->saveInto($wishlist);
        // Optional: Setze weitere Felder, z.B. CreatorID, Hash etc.
        $wishlist->write();
        $form->sessionMessage('Wunschliste wurde angelegt!', 'good');
        return $this->redirect('wishlist/ownerview/' . $wishlist->OwnerHash);
    }

    public function doEditWishlist($data, $form)
    {
        $hash = $data['OwnerHash'] ?? null;
        $wishlist = Wishlist::get()->filter('OwnerHash', $hash)->first();
        if(!$wishlist) {
            $form->sessionMessage('Wunschliste nicht gefunden!', 'bad');
            return $this->redirectBack();
        }
        $form->saveInto($wishlist);
        $wishlist->write();
        $form->sessionMessage('Wunschliste wurde aktualisiert!', 'good');
        return $this->redirectBack();
    }

    public function AddWishForm()
    {
        $hash = $this->getRequest()->param('ID');
        $wishlist = Wishlist::get()->filter('OwnerHash', $hash)->first();
        if(!$wishlist) {
            return null;
        }
        $fields = FieldList::create(
            TextField::create('Title', 'Wunsch'),
            TextField::create('Description', 'Beschreibung'),
            TextField::create('Price', 'Preis')->setAttribute('placeholder', 'Optional'),
            TextField::create('URL', 'Link')->setAttribute('placeholder', 'Optional'),
            HiddenField::create('ParentID', 'ParentID', $wishlist->ID),
            HiddenField::create('OwnerHash', 'OwnerHash', $wishlist->OwnerHash)
        );
        $actions = FieldList::create(
            FormAction::create('doAddWish', 'Wunsch hinzufügen')
        );
        $validator = RequiredFieldsValidator::create('Title');
        $form = Form::create($this, 'AddWishForm', $fields, $actions, $validator);
        // Setze FormAction auf den Controller-Link, damit der Handler im richtigen Kontext aufgerufen wird
        $form->setFormAction($this->Link('edit/' . $hash));
        return $form;
    }

    public function doAddWish($data, $form)
    {
        // Sofortiges Feedback, dass Handler aufgerufen wurde
        $form->sessionMessage('DEBUG: doAddWish wurde aufgerufen!', 'warning');

        error_log('=== doAddWish START ===');
        error_log('Data: ' . print_r($data, true));

        $wishlist = null;
        if (!empty($data['ParentID'])) {
            error_log('Suche Wishlist mit ParentID: ' . $data['ParentID']);
            $wishlist = Wishlist::get()->byID($data['ParentID']);
        } elseif (!empty($data['OwnerHash'])) {
            error_log('Suche Wishlist mit OwnerHash: ' . $data['OwnerHash']);
            $wishlist = Wishlist::get()->filter('OwnerHash', $data['OwnerHash'])->first();
        }
        if(!$wishlist) {
            error_log('FEHLER: Wishlist nicht gefunden!');
            $form->sessionMessage('Wunschliste nicht gefunden!', 'bad');
            return $this->redirectBack();
        }
        error_log('Wishlist gefunden: ID=' . $wishlist->ID . ', Title=' . $wishlist->Title);

        $wish = Wish::create();
        $form->saveInto($wish);
        $wish->ParentID = $wishlist->ID;

        error_log('Wish vor write(): Title=' . $wish->Title . ', ParentID=' . $wish->ParentID);
        $wish->write();
        error_log('Wish nach write(): ID=' . $wish->ID);
        error_log('=== doAddWish ENDE ===');

        $form->sessionMessage('Wunsch wurde hinzugefügt!', 'good');
        echo "Test";
        //return $this->redirectBack();
    }
}
