<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

/**
 * Optimiza imágenes antes de guardarlas.
 *
 * Perfil    | Formato | Máx px       | Notas
 * --------- | ------- | ------------ | -----------------------------------
 * texture   | WebP 88 | 1400 × 1400  | Alpha soportado, ~80% menos que PNG
 * thumbnail | WebP 80 | 600  × 600   | Miniatura del material
 * base      | WebP 85 | 2400 × 2400  | Imagen base del ambiente
 * overlay   | WebP 90 | 2400 × 2400  | Sombra / luz / foreground (alpha)
 * mask      | WebP 100| 2400 × 2400  | Alpha exacto, lossless via calidad 100
 * preview   | WebP 80 | 1200 × 1200  | Preview card del ambiente
 * default   | WebP 82 | 1800 × 1800  | Cualquier otra imagen
 */
class ImageOptimizer
{
    private const PROFILES = [
        'texture'   => ['max_w' => 1400, 'max_h' => 1400, 'quality' => 88],
        'thumbnail' => ['max_w' => 600,  'max_h' => 600,  'quality' => 80],
        'base'      => ['max_w' => 2400, 'max_h' => 2400, 'quality' => 85],
        'overlay'   => ['max_w' => 2400, 'max_h' => 2400, 'quality' => 90],
        'mask'      => ['max_w' => 2400, 'max_h' => 2400, 'quality' => 100],
        'preview'   => ['max_w' => 1200, 'max_h' => 1200, 'quality' => 80],
        'default'   => ['max_w' => 1800, 'max_h' => 1800, 'quality' => 82],
    ];

    private ImageManager $manager;

    public function __construct()
    {
        // Usa Imagick si está disponible; si no, GD como fallback
        $this->manager = extension_loaded('imagick')
            ? new ImageManager(new ImagickDriver())
            : new ImageManager(new GdDriver());
    }

    /**
     * Optimiza y guarda en Storage::disk('public').
     * Devuelve la ruta relativa guardada (siempre .webp).
     */
    public function store(UploadedFile $file, string $folder, string $profile = 'default'): string
    {
        $cfg      = self::PROFILES[$profile] ?? self::PROFILES['default'];
        $before   = $file->getSize();
        $original = $file->getClientOriginalName();

        try {
            $image = $this->manager->read($file->getPathname());

            // Escala solo si excede el máximo (nunca amplía)
            $image->scaleDown($cfg['max_w'], $cfg['max_h']);

            // Todo sale como WebP — soporta alpha, mejor compresión que PNG/JPEG
            $encoded = $image->toWebp($cfg['quality']);
            $content = (string) $encoded;

        } catch (\Throwable $e) {
            // Fallback: guardar original sin optimizar
            Log::warning("ImageOptimizer fallback para '{$original}': {$e->getMessage()}");
            $content = file_get_contents($file->getPathname());

            // Guardar con extensión original
            $ext  = strtolower($file->getClientOriginalExtension() ?: 'png');
            $path = trim($folder, '/') . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, $content);

            Log::info("ImageOptimizer: guardado sin optimizar → {$path}");
            return $path;
        }

        $path  = trim($folder, '/') . '/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($path, $content);

        $after = strlen($content);
        $pct   = $before > 0 ? round((1 - $after / $before) * 100, 1) : 0;
        $driver = extension_loaded('imagick') ? 'Imagick' : 'GD';

        Log::info("ImageOptimizer [{$driver}] {$original} → {$path} | {$this->humanSize($before)} → {$this->humanSize($after)} ({$pct}% reducción)");

        return $path;
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
