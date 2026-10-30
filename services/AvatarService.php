<?php
/**
 * services/AvatarService.php
 * Profile photo upload: validates, resizes to a small square, and stores it.
 * Reuses the same private-storage pattern as document uploads.
 */
class AvatarService
{
    const MAX_BYTES = 5 * 1024 * 1024;   // 5 MB before resizing
    const SIZE = 320;                     // stored square size in pixels

    /** Returns [error|null, GD image resource|null]. Only JPG/PNG accepted. */
    public static function validate(?array $file): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['Please choose a photo.', null];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['The upload failed. Please try again.', null];
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['The upload failed. Please try again.', null];
        }
        if ($file['size'] > self::MAX_BYTES) {
            return ['The photo is too large. The maximum size is ' . (self::MAX_BYTES / 1048576) . ' MB.', null];
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return ['Please upload a JPG or PNG photo.', null];
        }

        $info = @getimagesize($file['tmp_name']);
        if (!$info) {
            return ['The photo could not be read. Please try another file.', null];
        }

        $src = $info[2] === IMAGETYPE_PNG ? @imagecreatefrompng($file['tmp_name']) : @imagecreatefromjpeg($file['tmp_name']);
        if (!$src) {
            return ['The photo could not be read. Please try another file.', null];
        }

        return [null, $src];
    }

    /**
     * Crops to a centered square, resizes to SIZE x SIZE, saves as JPEG.
     * Deletes the user's previous avatar file, if any.
     */
    public static function storeForUser(int $userId, $srcImage, ?string $previousPath): string
    {
        $w = imagesx($srcImage);
        $h = imagesy($srcImage);
        $side = min($w, $h);
        $srcX = (int)(($w - $side) / 2);
        $srcY = (int)(($h - $side) / 2);

        $square = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagecopyresampled($square, $srcImage, 0, 0, $srcX, $srcY, self::SIZE, self::SIZE, $side, $side);
        imagedestroy($srcImage);

        $relative = 'avatars/' . bin2hex(random_bytes(16)) . '.jpg';
        $absolute = ROOT_PATH . '/storage/' . $relative;
        $dir = dirname($absolute);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create the storage folder.');
        }
        imagejpeg($square, $absolute, 88);
        imagedestroy($square);

        if ($previousPath) {
            $old = UploadService::absolutePath($previousPath);
            if ($old) {
                @unlink($old);
            }
        }

        return $relative;
    }

    public static function remove(?string $path): void
    {
        if (!$path) {
            return;
        }
        $abs = UploadService::absolutePath($path);
        if ($abs) {
            @unlink($abs);
        }
    }
}