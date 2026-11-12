<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

class WebpConverterService
{
    /**
     * Varsayılan WebP sıkıştırma kalitesi (0-100).
     * 82, görselde fark edilmeyen kayıpla maksimum boyut tasarrufu sağlar.
     */
    public const DEFAULT_QUALITY = 82;

    /**
     * Varsayılan maksimum piksel boyutu (en uzun kenar için).
     * 1920 piksel, yüksek çözünürlüklü ekranlar için mükemmel netlik sağlarken
     * aşırı büyük kamera çıktılarını dengeler.
     */
    public const DEFAULT_MAX_DIMENSION = 1920;

    /**
     * Yüklenen dosyayı WebP formatına dönüştürüp hedeflenen diske ve dizine kaydeder.
     *
     * @param TemporaryUploadedFile|UploadedFile|string $file
     * @param string|null $disk
     * @param string|null $directory
     * @param int $quality
     * @param int|null $maxDimension
     * @return string|null Kaydedilen göreli dosya yolu (örn: products/xyz.webp)
     */
    public function convertAndStore(
        $file,
        ?string $disk = 'public',
        ?string $directory = null,
        int $quality = self::DEFAULT_QUALITY,
        ?int $maxDimension = self::DEFAULT_MAX_DIMENSION
    ): ?string {
        $disk = $disk ?: 'public';
        $directory = trim((string) $directory, '/');

        $sourcePath = $this->resolveFilePath($file);

        if (! $sourcePath || ! file_exists($sourcePath)) {
            return null;
        }

        // SVG veya animasyonlu / dönüştürülemeyen dosyalar için doğrudan kaydet
        if ($this->isSvg($file, $sourcePath)) {
            return $this->fallbackStore($file, $sourcePath, $disk, $directory);
        }

        try {
            // Görseli bellek nesnesi olarak yükle
            $sourceImage = $this->createImageFromSource($sourcePath);

            if (! $sourceImage) {
                return $this->fallbackStore($file, $sourcePath, $disk, $directory);
            }

            // EXIF yönelimini düzelt (telefon fotoğraflarının yan yatmasını önler)
            $sourceImage = $this->fixOrientation($sourceImage, $sourcePath);

            $origWidth = imagesx($sourceImage);
            $origHeight = imagesy($sourceImage);

            if ($origWidth <= 0 || $origHeight <= 0) {
                $this->freeImage($sourceImage);
                return $this->fallbackStore($file, $sourcePath, $disk, $directory);
            }

            // Yeni boyutları hesapla (en boy oranını koruyarak)
            [$targetWidth, $targetHeight] = $this->calculateDimensions($origWidth, $origHeight, $maxDimension);

            // Hedef tuvali oluştur ve saydamlığı (alpha) koru
            $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);

            // Yeniden örnekleme (resample) ile yüksek kaliteli ölçekleme
            imagecopyresampled(
                $targetImage,
                $sourceImage,
                0, 0, 0, 0,
                $targetWidth,
                $targetHeight,
                $origWidth,
                $origHeight
            );

            // WebP desteği kontrolü
            if (! function_exists('imagewebp')) {
                $this->freeImage($sourceImage);
                $this->freeImage($targetImage);
                return $this->fallbackStore($file, $sourcePath, $disk, $directory);
            }

            // Geçici WebP dosyasına yaz
            $tempWebpPath = tempnam(sys_get_temp_dir(), 'webp_') . '.webp';
            $success = imagewebp($targetImage, $tempWebpPath, $quality);

            $this->freeImage($sourceImage);
            $this->freeImage($targetImage);

            if (! $success || ! file_exists($tempWebpPath)) {
                return $this->fallbackStore($file, $sourcePath, $disk, $directory);
            }

            // Benzersiz dosya adı oluştur
            $filename = Str::random(40) . '.webp';
            $targetPath = $directory !== '' ? "{$directory}/{$filename}" : $filename;

            // Diske kaydet
            $contents = file_get_contents($tempWebpPath);
            Storage::disk($disk)->put($targetPath, $contents, 'public');

            @unlink($tempWebpPath);

            return $targetPath;
        } catch (Throwable $e) {
            Log::warning('WebP dönüştürme başarısız oldu, orijinal dosya kaydediliyor: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return $this->fallbackStore($file, $sourcePath, $disk, $directory);
        }
    }

    /**
     * Dosya yolunu çözer.
     */
    protected function resolveFilePath($file): ?string
    {
        if (is_string($file)) {
            return $file;
        }

        if ($file instanceof TemporaryUploadedFile || $file instanceof UploadedFile) {
            return $file->getRealPath();
        }

        if (is_object($file) && method_exists($file, 'getRealPath')) {
            return $file->getRealPath();
        }

        return null;
    }

    /**
     * SVG kontrolü yapar.
     */
    protected function isSvg($file, string $sourcePath): bool
    {
        $extension = '';
        if ($file instanceof UploadedFile || $file instanceof TemporaryUploadedFile) {
            $extension = strtolower((string) $file->getClientOriginalExtension());
        } else {
            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        }

        if ($extension === 'svg') {
            return true;
        }

        $mime = @mime_content_type($sourcePath);
        return $mime === 'image/svg+xml' || $mime === 'image/svg';
    }

    /**
     * Kaynak dosyadan GD görsel nesnesi oluşturur.
     *
     * @return GdImage|resource|false
     */
    protected function createImageFromSource(string $sourcePath)
    {
        $imageInfo = @getimagesize($sourcePath);
        if (! $imageInfo) {
            return false;
        }

        $mime = $imageInfo['mime'] ?? '';

        switch ($mime) {
            case 'image/jpeg':
            case 'image/pjpeg':
                return @imagecreatefromjpeg($sourcePath);

            case 'image/png':
            case 'image/x-png':
                return @imagecreatefrompng($sourcePath);

            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    return @imagecreatefromwebp($sourcePath);
                }
                return false;

            case 'image/gif':
                return @imagecreatefromgif($sourcePath);

            case 'image/bmp':
            case 'image/x-ms-bmp':
                if (function_exists('imagecreatefrombmp')) {
                    return @imagecreatefrombmp($sourcePath);
                }
                return false;

            default:
                return false;
        }
    }

    /**
     * Mobil cihazlarda çekilen fotoğrafların EXIF yönelimini (Orientation) düzeltir.
     *
     * @param GdImage|resource $image
     * @return GdImage|resource
     */
    protected function fixOrientation($image, string $sourcePath)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($sourcePath);
            if (! empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 2:
                        imageflip($image, IMG_FLIP_HORIZONTAL);
                        break;
                    case 3:
                        $rotated = imagerotate($image, 180, 0);
                        if ($rotated !== false) {
                            $this->freeImage($image);
                            $image = $rotated;
                        }
                        break;
                    case 4:
                        imageflip($image, IMG_FLIP_VERTICAL);
                        break;
                    case 5:
                        imageflip($image, IMG_FLIP_VERTICAL);
                        $rotated = imagerotate($image, -90, 0);
                        if ($rotated !== false) {
                            $this->freeImage($image);
                            $image = $rotated;
                        }
                        break;
                    case 6:
                        $rotated = imagerotate($image, -90, 0);
                        if ($rotated !== false) {
                            $this->freeImage($image);
                            $image = $rotated;
                        }
                        break;
                    case 7:
                        imageflip($image, IMG_FLIP_HORIZONTAL);
                        $rotated = imagerotate($image, -90, 0);
                        if ($rotated !== false) {
                            $this->freeImage($image);
                            $image = $rotated;
                        }
                        break;
                    case 8:
                        $rotated = imagerotate($image, 90, 0);
                        if ($rotated !== false) {
                            $this->freeImage($image);
                            $image = $rotated;
                        }
                        break;
                }
            }
        } catch (Throwable $e) {
            // EXIF okuma hatası önemsizdir, görsel olduğu gibi kalır
        }

        return $image;
    }

    /**
     * En boy oranını koruyarak yeni genişlik ve yükseklik hesaplar.
     *
     * @return array{0: int, 1: int}
     */
    protected function calculateDimensions(int $width, int $height, ?int $maxDimension): array
    {
        if (! $maxDimension || ($width <= $maxDimension && $height <= $maxDimension)) {
            return [$width, $height];
        }

        if ($width >= $height) {
            $newWidth = $maxDimension;
            $newHeight = (int) round(($height / $width) * $maxDimension);
        } else {
            $newHeight = $maxDimension;
            $newWidth = (int) round(($width / $height) * $maxDimension);
        }

        return [max(1, $newWidth), max(1, $newHeight)];
    }

    /**
     * Dönüştürme yapılamazsa orijinal dosyayı doğrudan kaydeder (yedek güvenli adım).
     */
    protected function fallbackStore($file, string $sourcePath, string $disk, string $directory): ?string
    {
        $extension = 'jpg';
        if ($file instanceof UploadedFile || $file instanceof TemporaryUploadedFile) {
            $extension = strtolower((string) $file->getClientOriginalExtension()) ?: 'jpg';
        } else {
            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) ?: 'jpg';
        }

        $filename = Str::random(40) . '.' . $extension;
        $targetPath = $directory !== '' ? "{$directory}/{$filename}" : $filename;

        Storage::disk($disk)->put($targetPath, file_get_contents($sourcePath), 'public');

        return $targetPath;
    }

    /**
     * GD görsel kaynağını güvenle bellekten temizler.
     *
     * @param GdImage|resource $image
     */
    protected function freeImage($image): void
    {
        if ($image instanceof GdImage || is_resource($image)) {
            @imagedestroy($image);
        }
    }
}
