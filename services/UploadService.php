<?php
/**
 * services/UploadService.php
 * Validates and stores uploaded files safely.
 * Files go in /storage (blocked from the web) under a random name.
 */
class UploadService
{
    const MAX_BYTES = 15 * 1024 * 1024;     // 15 MB

    /** Allowed extensions and the MIME types we accept for each. */
    const TYPES = [
        'jpg'  => ['image/jpeg'],
        'png'  => ['image/png'],
        'pdf'  => ['application/pdf'],
        // Word/Excel files are ZIP containers, so finfo may report zip/octet-stream.
        // We double-check them by opening the ZIP below.
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
    ];

    /** MIME type to send when downloading a stored file. */
    public static function mimeFor(string $ext): string
    {
        return [
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ][$ext] ?? 'application/octet-stream';
    }

    /**
     * Checks an entry of $_FILES.
     * Returns [error message or null, normalised extension or null].
     */
    public static function validate(?array $file): array
    {
        $none = ['Please take a photo or choose a file.', null];

        if (!$file || !isset($file['error'])) {
            return $none;
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return $none;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['The file is too large. The maximum size is ' . (self::MAX_BYTES / 1048576) . ' MB.', null];
            default:
                return ['The upload failed. Please try again.', null];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['The upload failed. Please try again.', null];
        }
        if ($file['size'] > self::MAX_BYTES) {
            return ['The file is too large. The maximum size is ' . (self::MAX_BYTES / 1048576) . ' MB.', null];
        }

        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if (!isset(self::TYPES[$ext])) {
            return ['This file type is not allowed. Use JPG, PNG, PDF, Word (.docx) or Excel (.xlsx).', null];
        }

        // Check the REAL content, not just the name the user gave the file
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::TYPES[$ext], true)) {
            return ['The file content does not match its type. Please choose a valid file.', null];
        }

        if (in_array($ext, ['jpg', 'png'], true) && @getimagesize($file['tmp_name']) === false) {
            return ['The image could not be read. Please try another photo.', null];
        }

        if (in_array($ext, ['docx', 'xlsx'], true) && class_exists('ZipArchive')) {
            $inner = $ext === 'docx' ? 'word/document.xml' : 'xl/workbook.xml';
            $zip   = new ZipArchive();
            $ok    = $zip->open($file['tmp_name']) === true && $zip->locateName($inner) !== false;
            if ($zip->status === ZipArchive::ER_OK) {
                $zip->close();
            }
            if (!$ok) {
                return ['That Office file looks damaged or is not a real .' . $ext . ' file.', null];
            }
        }

        return [null, $ext];
    }

    /** Moves the file into storage. Returns ['path' => relative path, 'size' => bytes, 'original' => clean name]. */
    public static function store(array $file, string $ext): array
    {
        $relative = 'documents/' . date('Y/m') . '/' . bin2hex(random_bytes(16)) . '.' . $ext;
        $absolute = ROOT_PATH . '/storage/' . $relative;
        $dir      = dirname($absolute);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create the storage folder.');
        }
        if (!move_uploaded_file($file['tmp_name'], $absolute)) {
            throw new RuntimeException('Could not save the uploaded file.');
        }

        $original = basename(str_replace('\\', '/', (string)$file['name']));
        $original = preg_replace('/[\x00-\x1F\x7F]+/', '', $original);

        return [
            'path'     => $relative,
            'size'     => (int)filesize($absolute),
            'original' => mb_substr($original, 0, 200),
        ];
    }

    /** Full disk path for a stored file, or null if it is missing / outside /storage. */
    public static function absolutePath(?string $relative): ?string
    {
        if (!$relative) {
            return null;
        }
        $base = realpath(ROOT_PATH . '/storage');
        $abs  = realpath(ROOT_PATH . '/storage/' . $relative);

        if ($base === false || $abs === false
            || strpos($abs, $base . DIRECTORY_SEPARATOR) !== 0
            || !is_file($abs)) {
            return null;
        }
        return $abs;
    }

    public static function delete(string $relative): void
    {
        $abs = self::absolutePath($relative);
        if ($abs) {
            @unlink($abs);
        }
    }
}