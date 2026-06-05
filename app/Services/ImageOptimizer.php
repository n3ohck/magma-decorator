<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

/**
 * Optimiza imágenes antes de guardarlas en storage.
 *
 * Estrategia por tipo:
 *  - texture   → PNG, max 1200×1200, compresión máxima, preserva alpha
 *  - thumbnail → WebP, max 600×600,  calidad 82 %
 *  - base      → WebP, max 2400 px ancho, calidad 85 %
 *  - overlay   → PNG, max 2400 px ancho, lossless (requiere alpha exacto)
 *  - mask      → PNG, max 2400 px ancho, lossless (alpha binario crítico)
 *  - preview   → WebP, max 1200 px ancho, calidad 80 %
 *  - default   → WebP, max 1800 px ancho, calidad 82 %
 */
class ImageOptimizer
{
    private const PROFILES = [
        'texture'   => ['format' => 'png',  'max_w' => 1200, 'max_h' => 1200, 'quality' => 9,   'lossless' => true],
        'thumbnail' => ['format' => 'webp', 'max_w' => 600,  'max_h' => 600,  'quality' => 82,  'lossless' => false],
        'base'      => ['format' => 'webp', 'max_w' => 2400, 'max_h' => 2400, 'quality' => 85,  'lossless' => false],
        'overlay'   => ['format' => 'png',  'max_w' => 2400, 'max_h' => 2400, 'quality' => 9,   'lossless' => true],
        'mask'      => ['format' => 'png',  'max_w' => 2400, 'max_h' => 2400, 'quality' => 9,   'lossless' => true],
        'preview'   => ['format' => 'webp', 'max_w' => 1200, 'max_h' => 1200, 'quality' => 80,  'lossless' => false],
        'default'   => ['format' => 'webp', 'max_w' => 1800, 'max_h' => 1800, 'quality' => 82,  'lossless' => false],
    ];

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new ImagickDriver());
    }

    /**
     * Optimiza un UploadedFile y devuelve el contenido binario listo para Storage::put().
     *
     * @param  UploadedFile  $file
     * @param  string        $profile  Uno de: texture, thumbnail, base, overlay, mask, preview, default
     * @return array{content: string, extension: string, mime: string}
     */
    public function optimize(UploadedFile $file, string $profile = 'default'): array
    {
        $cfg = self::PROFILES[$profile] ?? self::PROFILES['default'];

        try {
            $image = $this->manager->read($file->getPathname());

            // Escalar solo si excede las dimensiones máximas (nunca amplía)
            $image->scaleDown($cfg['max_w'], $cfg['max_h']);

            // Codificar al formato objetivo
            $encoded = match ($cfg['format']) {
                'webp' => $image->toWebp($cfg['quality']),
                'png'  => $image->toPng(indexed: false),     // PNG-24 comprimido
                'jpeg' => $image->toJpeg($cfg['quality']),
                default => $image->toWebp($cfg['quality']),
            };

            $mime = match ($cfg['format']) {
                'webp'  => 'image/webp',
                'png'   => 'image/png',
                'jpeg'  => 'image/jpeg',
                default => 'image/webp',
            };

            return [
                'content'   => (string) $encoded,
                'extension' => $cfg['format'] === 'jpeg' ? 'jpg' : $cfg['format'],
                'mime'      => $mime,
                'original'  => $file->getClientOriginalName(),
            ];

        } catch (\Throwable $e) {
            // Fallback: si falla la optimización, devuelve el archivo original sin tocar
            Log::warning("ImageOptimizer: falló optimización, usando original. {$e->getMessage()}", [
                'file'    => $file->getClientOriginalName(),
                'profile' => $profile,
            ]);

            return [
                'content'   => file_get_contents($file->getPathname()),
                'extension' => $file->getClientOriginalExtension() ?: 'png',
                'mime'      => $file->getMimeType() ?: 'image/png',
                'original'  => $file->getClientOriginalName(),
            ];
        }
    }

    /**
     * Optimiza y guarda directamente en Storage::disk('public').
     * Devuelve la ruta relativa guardada (incluyendo la nueva extensión).
     *
     * @param  UploadedFile  $file
     * @param  string        $folder   Ejemplo: 'materials/textures'
     * @param  string        $profile
     * @return string  Ruta relativa, ej: 'materials/textures/abc123.webp'
     */
    public function store(UploadedFile $file, string $folder, string $profile = 'default'): string
    {
        $result    = $this->optimize($file, $profile);
        $filename  = \Illuminate\Support\Str::uuid() . '.' . $result['extension'];
        $path      = trim($folder, '/') . '/' . $filename;

        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $result['content']);

        $original  = $file->getClientOriginalName();
        $before    = $file->getSize();
        $after     = strlen($result['content']);
        $saved     = $before > 0 ? round((1 - $after / $before) * 100, 1) : 0;

        Log::info("ImageOptimizer: {$original} → {$path} ({$saved}% reducción, {$before}→{$after} bytes)");

        return $path;
    }
}
