<?php

namespace App\Api;

use App\Model\Wish;

/**
 * Übernimmt die erlaubten Wunsch-Felder aus dem Request-Body.
 * Nur in ApiController-Unterklassen verwenden.
 */
trait WishFieldsTrait
{
    protected function applyWishFields(Wish $wish, bool $isNew): void
    {
        $title = $this->stringField('title', 255, 'Der Titel');
        if ($title !== null || $isNew) {
            if ((string) $title === '') {
                $this->fail('Bitte gib einen Titel für den Wunsch ein.', 400);
            }
            $wish->Title = $title;
        }

        $body = $this->body();
        if (array_key_exists('priority', $body)) {
            $priority = $body['priority'];
            if (!is_int($priority) || $priority < Wish::PRIORITY_MIN || $priority > Wish::PRIORITY_MAX) {
                $this->fail('Die Stärke muss eine Zahl von ' . Wish::PRIORITY_MIN . ' bis ' . Wish::PRIORITY_MAX . ' sein.', 400);
            }
            $wish->Priority = $priority;
        }

        if (array_key_exists('hidden', $body)) {
            if (!is_bool($body['hidden'])) {
                $this->fail('Ungültiger Wert für die Sichtbarkeit.', 400);
            }
            $wish->Hidden = $body['hidden'];
        }

        $type = $this->stringField('type', 100, 'Der Typ');
        if ($type !== null) {
            $wish->Type = $type;
        }

        $description = $this->stringField('description', 5000, 'Die Beschreibung');
        if ($description !== null) {
            $wish->Description = $description;
        }

        $price = $this->stringField('price', 100, 'Der Preis');
        if ($price !== null) {
            $wish->Price = $price;
        }

        $url = $this->stringField('url', 2048, 'Der Link');
        if ($url !== null) {
            $normalized = Wish::normalizeUrl($url);
            if ($normalized === null) {
                $this->fail('Bitte gib einen gültigen Link (http oder https) ein.', 400);
            }
            $wish->URL = $normalized;
        }
    }
}
