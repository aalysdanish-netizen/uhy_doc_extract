<?php
/**
 * services/OcrService.php
 * Runs Tesseract on an image and returns plain text.
 * This is the ONLY place that knows how OCR is done - swap the engine here later
 * (e.g. a cloud OCR API) without touching anything else in the app.
 */
class OcrService
{
    const SUPPORTED_EXT = ['jpg', 'png'];

    public static function supports(string $ext): bool
    {
        return in_array(strtolower($ext), self::SUPPORTED_EXT, true);
    }

    /**
     * Returns ['text' => string, 'engine' => string] or throws RuntimeException
     * with a message that is safe to show to the user.
     */
    public static function extract(string $imagePath): array
    {
        if (!is_file($imagePath)) {
            throw new RuntimeException('The image file could not be found.');
        }
        if (!is_file(TESSERACT_PATH)) {
            throw new RuntimeException('The OCR engine is not installed on this server. Please check TESSERACT_PATH in config/app.php.');
        }

        $prepped     = self::preprocess($imagePath);   // returns null if GD is unavailable
        $workingPath = $prepped ?? $imagePath;
        $outputBase  = sys_get_temp_dir() . '/df_ocr_' . bin2hex(random_bytes(8));

        $cmd = escapeshellarg(TESSERACT_PATH) . ' ' . escapeshellarg($workingPath) . ' '
             . escapeshellarg($outputBase) . ' -l ' . escapeshellarg(OCR_LANGUAGE) . ' --psm 6';

        $process = @proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('Could not start the OCR engine.');
        }
        stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($prepped) {
            @unlink($prepped);
        }

        $textFile = $outputBase . '.txt';
        if ($exitCode !== 0 || !is_file($textFile)) {
            throw new RuntimeException('The document could not be read. Please try a clearer photo.' . (APP_DEBUG ? ' (' . trim($stderr) . ')' : ''));
        }

        $text = (string)file_get_contents($textFile);
        @unlink($textFile);

        return ['text' => trim($text), 'engine' => 'tesseract'];
    }

    /**
     * Grayscale + a contrast boost. This measurably helps Tesseract read phone photos
     * that have shadows or an off-white background. Returns a temp file path,
     * or null if GD is unavailable (OCR still runs on the original image).
     */
    private static function preprocess(string $imagePath): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }
        $info = @getimagesize($imagePath);
        if (!$info) {
            return null;
        }

        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG  => @imagecreatefrompng($imagePath),
            default        => null,
        };
        if (!$src) {
            return null;
        }

        // OCR gains nothing above ~2500px on the long edge, so cap it to keep things fast.
        $w = imagesx($src);
        $h = imagesy($src);
        $max = 2500;
        if (max($w, $h) > $max) {
            $scale = $max / max($w, $h);
            $nw = (int)round($w * $scale);
            $nh = (int)round($h * $scale);
            $resized = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $resized;
        }

        imagefilter($src, IMG_FILTER_GRAYSCALE);
        imagefilter($src, IMG_FILTER_CONTRAST, -12);   // negative = MORE contrast with GD

        $temp = sys_get_temp_dir() . '/df_prep_' . bin2hex(random_bytes(8)) . '.jpg';
        imagejpeg($src, $temp, 90);
        imagedestroy($src);

        return is_file($temp) ? $temp : null;
    }
}