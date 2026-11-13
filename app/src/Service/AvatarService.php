<?php

namespace App\Service;

use App\Model\User;
use RuntimeException;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

/**
 * Verwaltet Profilbilder.
 *
 * Jeder User hat einen eigenen Ordner "Profilbilder/{ID}" mit genau einer Datei.
 * Beim Speichern eines neuen Bildes werden alle bisherigen Dateien dieses Ordners
 * physisch gelöscht, es gibt also nie mehr als ein Profilbild pro User.
 */
class AvatarService
{
    use Injectable;
    use Configurable;

    /** Maximale Kantenlänge des gespeicherten Bildes in Pixeln */
    private static int $max_size = 800;

    /** Maximale Größe der hochgeladenen Datei in Bytes */
    private static int $max_upload_bytes = 15 * 1024 * 1024;

    /** Schutz vor "Dekompressionsbomben": maximale Pixelanzahl des Eingangsbildes */
    private static int $max_input_pixels = 50_000_000;

    private static string $base_folder = 'Profilbilder';

    /**
     * Prüft das hochgeladene Bild, schneidet es quadratisch zu, skaliert es auf
     * höchstens max_size und speichert es als neues Profilbild des Users.
     *
     * @throws RuntimeException mit deutscher Fehlermeldung
     */
    public function store(User $user, string $data): Image
    {
        $jpeg = $this->normalizeImage($data);

        $folder = Folder::find_or_make($this->folderPath($user));
        $this->deleteFilesInFolder($folder);

        $image = Image::create();
        $image->setFromString($jpeg, $folder->getFilename() . 'profilbild.jpg');
        $image->ParentID = $folder->ID;
        $image->Title = 'Profilbild ' . $user->getTitle();
        $image->write();
        $image->publishSingle();

        $user->AvatarID = $image->ID;
        $user->write();

        return $image;
    }

    /**
     * Entfernt das Profilbild des Users vollständig (Datei und Datensatz).
     * Mit $updateUser = false wird der User selbst nicht gespeichert (z. B. beim Löschen des Users).
     */
    public function remove(User $user, bool $updateUser = true): void
    {
        $image = $user->Avatar();
        if ($image && $image->exists()) {
            $folder = $image->Parent();
            $this->deleteFully($image);
            if ($folder && $folder->exists()) {
                $this->deleteFilesInFolder($folder);
            }
        }

        if ($updateUser) {
            $user->AvatarID = 0;
            $user->write();
        }
    }

    /**
     * Öffentliche URL des Profilbilds inkl. Cache-Buster, oder null
     */
    public function url(User $user): ?string
    {
        $image = $user->Avatar();
        if (!$image || !$image->exists()) {
            return null;
        }
        $url = $image->getURL();
        return $url ? $url . '?v=' . substr((string) $image->getHash(), 0, 10) : null;
    }

    /**
     * Dekodiert das Bild, schneidet mittig quadratisch zu, skaliert auf max. max_size
     * und kodiert neu als JPEG. Dabei gehen auch alle Metadaten (EXIF, GPS) verloren.
     */
    public function normalizeImage(string $data): string
    {
        if ($data === '' || strlen($data) > static::config()->get('max_upload_bytes')) {
            throw new RuntimeException('Das Bild ist zu groß (maximal 15 MB).');
        }

        $info = @getimagesizefromstring($data);
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
        if (!$info || !in_array($info[2], $allowedTypes, true)) {
            throw new RuntimeException('Bitte lade ein Bild im Format JPG, PNG, WebP oder GIF hoch.');
        }

        [$width, $height] = $info;
        if ($width < 1 || $height < 1 || $width * $height > static::config()->get('max_input_pixels')) {
            throw new RuntimeException('Die Auflösung des Bildes ist zu groß.');
        }

        $source = @imagecreatefromstring($data);
        if (!$source) {
            throw new RuntimeException('Das Bild konnte nicht gelesen werden.');
        }

        $side = min($width, $height);
        $srcX = intdiv($width - $side, 2);
        $srcY = intdiv($height - $side, 2);
        $target = min(static::config()->get('max_size'), $side);

        $canvas = imagecreatetruecolor($target, $target);
        // Transparente Bereiche (PNG/WebP) auf die Flächenfarbe der App legen
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 0x1E, 0x16, 0x23));
        imagecopyresampled($canvas, $source, 0, 0, $srcX, $srcY, $target, $target, $side, $side);

        ob_start();
        imagejpeg($canvas, null, 88);
        $jpeg = (string) ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return $jpeg;
    }

    private function folderPath(User $user): string
    {
        return static::config()->get('base_folder') . '/' . (int) $user->ID;
    }

    private function deleteFilesInFolder(Folder $folder): void
    {
        foreach (File::get()->filter('ParentID', $folder->ID) as $file) {
            if (!$file instanceof Folder) {
                $this->deleteFully($file);
            }
        }
    }

    /**
     * Löscht die physische Datei (inkl. aller Varianten) und den Datensatz aus Entwurf und Live.
     */
    private function deleteFully(File $file): void
    {
        $file->deleteFile();
        if ($file->hasExtension(\SilverStripe\Versioned\Versioned::class)) {
            $file->doArchive();
        } else {
            $file->delete();
        }
    }
}
