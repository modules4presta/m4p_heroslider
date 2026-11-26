<?php
/**
 * m4p_heroslider
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Slide files under img/m4p_heroslider/: a photo or poster in two widths and every format of
 * PS_IMAGE_FORMAT (<name>.<format>, <name>-m.<format>), and a video as uploaded.
 */
class M4pHeroMedia
{
    public const DIR = 'm4p_heroslider/';
    public const IMAGE_WIDTH = 2048;
    public const IMAGE_WIDTH_MOBILE = 960;
    public const IMAGE_MAX_BYTES = 8388608;
    public const VIDEO_TYPES = ['video/mp4' => 'mp4', 'video/webm' => 'webm'];

    /** @return string[] */
    public static function imageFormats(): array
    {
        $formats = array_filter(array_map('trim', explode(',', (string) Configuration::get('PS_IMAGE_FORMAT'))));
        $formats = array_values(array_intersect($formats, ['jpg', 'png', 'webp', 'avif']));

        return $formats ?: ['jpg'];
    }

    /**
     * Stores an image file; returns its base name, '' when it is not a usable image.
     */
    public static function storeImage(string $path): string
    {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        $size = @getimagesize($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || !$size || filesize($path) > self::IMAGE_MAX_BYTES) {
            return '';
        }
        if (!self::ensureDir()) {
            return '';
        }
        $name = 'slide-' . bin2hex(random_bytes(6));
        foreach ([[$name, self::IMAGE_WIDTH], [$name . '-m', self::IMAGE_WIDTH_MOBILE]] as [$file, $maxWidth]) {
            $width = min((int) $size[0], $maxWidth);
            $height = (int) round($size[1] * $width / max(1, (int) $size[0]));
            foreach (self::imageFormats() as $format) {
                if (!ImageManager::resize($path, self::path($file . '.' . $format), $width, $height, $format, true)) {
                    self::delete($name);

                    return '';
                }
            }
        }

        return $name;
    }

    /**
     * Moves an uploaded MP4 or WebM file in; returns its file name, '' when the content is not one.
     */
    public static function storeVideo(string $path, int $maxBytes): string
    {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!isset(self::VIDEO_TYPES[$mime]) || filesize($path) > $maxBytes || !self::ensureDir()) {
            return '';
        }
        $name = 'slide-' . bin2hex(random_bytes(6)) . '.' . self::VIDEO_TYPES[$mime];
        $moved = is_uploaded_file($path) ? move_uploaded_file($path, self::path($name)) : copy($path, self::path($name));

        return $moved ? $name : '';
    }

    /** Removes a stored video, or every width and format of a stored image. */
    public static function delete(string $name): void
    {
        if ($name === '' || !preg_match('/^slide-[0-9a-f]+(\.(mp4|webm))?$/', $name)) {
            return;
        }
        foreach (glob(self::path($name) . '*') ?: [] as $file) {
            unlink($file);
        }
    }

    /**
     * URLs of a stored image: format => [desktop, mobile], plus 'src' and 'src_m' for <img>.
     *
     * @return array<string, mixed>
     */
    public static function imageSources(string $name): array
    {
        if ($name === '') {
            return [];
        }
        $sources = ['formats' => []];
        foreach (self::imageFormats() as $format) {
            if (is_file(self::path($name . '.' . $format))) {
                $sources['formats'][$format] = [self::url($name . '.' . $format), self::url($name . '-m.' . $format)];
            }
        }
        if ($sources['formats'] === []) {
            return [];
        }
        $fallback = $sources['formats']['jpg'] ?? $sources['formats']['png'] ?? reset($sources['formats']);
        $sources['src'] = $fallback[0];
        $sources['src_m'] = $fallback[1];
        unset($sources['formats']['jpg'], $sources['formats']['png']);

        return $sources;
    }

    public static function url(string $file): string
    {
        return _PS_IMG_ . self::DIR . $file;
    }

    private static function path(string $file): string
    {
        return _PS_IMG_DIR_ . self::DIR . $file;
    }

    private static function ensureDir(): bool
    {
        $dir = _PS_IMG_DIR_ . self::DIR;
        if (is_dir($dir)) {
            return true;
        }
        if (!mkdir($dir, 0755, true)) {
            return false;
        }
        $guard = _PS_IMG_DIR_ . 'index.php';
        if (is_file($guard)) {
            copy($guard, $dir . 'index.php');
        }

        return true;
    }
}
