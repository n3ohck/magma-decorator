<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReplicateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SAMMaskController extends Controller
{
    /**
     * POST /admin/builder/sam-mask
     *
     * Receives a public image URL and a click point (as 0–1 fractions of the
     * image dimensions), runs SAM 2 via Replicate, downloads the resulting
     * mask PNG and saves it to storage.
     *
     * Returns:
     *   { mask_path: "environments/masks/sam_xxx.png", mask_url: "https://..." }
     *
     * Note: The image URL must be publicly accessible for Replicate to fetch it.
     */
    public function generate(Request $request, ReplicateService $replicate): JsonResponse
    {
        if (! $replicate->isConfigured()) {
            return response()->json(['error' => 'Replicate API token no configurado.'], 503);
        }

        $data = $request->validate([
            'image_data'   => ['required', 'string'],   // base64 data URI
            'x'            => ['required', 'numeric', 'min:0', 'max:1'],
            'y'            => ['required', 'numeric', 'min:0', 'max:1'],
            'image_width'  => ['required', 'integer', 'min:1'],
            'image_height' => ['required', 'integer', 'min:1'],
        ]);

        $pixelX = (int) round($data['x'] * $data['image_width']);
        $pixelY = (int) round($data['y'] * $data['image_height']);

        // Resize la imagen a max 1024px para reducir el payload y evitar timeouts
        $imageData = $this->resizeBase64Image($data['image_data'], 1024);

        // Versión de SAM 2 en Replicate
        $version = config(
            'services.replicate.sam_version',
            'fe97b453a6455861e3bac769b441ca1f1086110da7466dbb65cf1eecfd60dc83'
        );

        // Escalar coordenadas si la imagen fue redimensionada
        $scaledW = $data['image_width'];
        $scaledH = $data['image_height'];
        $maxSide = 1024;
        if ($scaledW > $maxSide || $scaledH > $maxSide) {
            $ratio   = min($maxSide / $scaledW, $maxSide / $scaledH);
            $scaledW = (int) round($scaledW * $ratio);
            $scaledH = (int) round($scaledH * $ratio);
            $pixelX  = (int) round($data['x'] * $scaledW);
            $pixelY  = (int) round($data['y'] * $scaledH);
        }

        $prediction = $replicate->createPrediction($version, [
            'image'            => $imageData,
            'input_points'     => [[$pixelX, $pixelY]],
            'input_labels'     => [1],
            'multimask_output' => false,
        ]);

        // SAM is fast — wait synchronously (timeout 90s)
        $result = $replicate->waitForResult($prediction['id'], maxSeconds: 90);

        // Log completo para diagnóstico
        Log::info('SAM prediction result', [
            'id'     => $prediction['id'],
            'status' => $result['status'],
            'output' => $result['output'] ?? null,
            'error'  => $result['error'] ?? null,
        ]);

        if ($result['status'] !== 'succeeded') {
            return response()->json([
                'error' => 'SAM no pudo generar la máscara: ' . ($result['error'] ?? $result['status']),
            ], 500);
        }

        // SAM 2 puede devolver el output en varios formatos:
        //   - array de URLs: ["https://...mask.png"]
        //   - objeto con clave masks: {"masks": ["https://...mask.png"]}
        //   - string URL directa: "https://...mask.png"
        $output  = $result['output'];
        $maskUrl = null;

        if (is_string($output) && str_starts_with($output, 'http')) {
            $maskUrl = $output;
        } elseif (is_array($output)) {
            // {"combined_mask": "...", "individual_masks": [...]}
            if (isset($output['combined_mask'])) {
                $maskUrl = $output['combined_mask'];
            }
            // {"individual_masks": [...]}
            elseif (isset($output['individual_masks'][0])) {
                $maskUrl = $output['individual_masks'][0];
            }
            // Array directo de URLs: ["https://..."]
            elseif (isset($output[0]) && is_string($output[0])) {
                $maskUrl = $output[0];
            }
            // {"masks": [...]}
            elseif (isset($output['masks'][0])) {
                $maskUrl = $output['masks'][0];
            }
            // {"mask": "..."}
            elseif (isset($output['mask']) && is_string($output['mask'])) {
                $maskUrl = $output['mask'];
            }
        }

        if (! $maskUrl) {
            Log::warning('SAM output format inesperado', ['output' => $output]);
            return response()->json([
                'error' => 'SAM no devolvió una imagen de máscara. Output: ' . json_encode($output),
            ], 500);
        }

        // Download and persist the mask
        $maskResponse = Http::timeout(30)->get($maskUrl);

        if (! $maskResponse->successful()) {
            return response()->json(['error' => 'No se pudo descargar la máscara generada.'], 500);
        }

        $filename = 'environments/masks/sam_' . Str::uuid() . '.png';
        Storage::disk('public')->put($filename, $maskResponse->body());

        return response()->json([
            'mask_path' => $filename,
            'mask_url'  => Storage::disk('public')->url($filename),
        ]);
    }

    /**
     * POST /admin/builder/mask-upload
     *
     * Recibe una máscara en base64 (generada desde el MaskEditor canvas),
     * la decodifica y la guarda en storage. Devuelve path y URL.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'mask_data' => ['required', 'string'],   // data:image/png;base64,...
        ]);

        $dataUrl = $request->string('mask_data')->toString();

        if (! str_starts_with($dataUrl, 'data:image/')) {
            return response()->json(['error' => 'Formato inválido.'], 422);
        }

        $base64  = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $content = base64_decode($base64);

        $filename = 'environments/masks/mask_' . Str::uuid() . '.png';
        Storage::disk('public')->put($filename, $content);

        return response()->json([
            'mask_path' => $filename,
            'mask_url'  => Storage::disk('public')->url($filename),
        ]);
    }

    /**
     * Redimensiona una imagen base64 al tamaño máximo indicado (mantiene ratio).
     * Reduce el payload enviado a Replicate y evita timeouts.
     */
    private function resizeBase64Image(string $dataUrl, int $maxSide): string
    {
        try {
            $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
            $binary = base64_decode($base64);

            $src = imagecreatefromstring($binary);
            if (! $src) return $dataUrl;

            $w = imagesx($src);
            $h = imagesy($src);

            if ($w <= $maxSide && $h <= $maxSide) {
                imagedestroy($src);
                return $dataUrl;
            }

            $ratio = min($maxSide / $w, $maxSide / $h);
            $nw    = (int) round($w * $ratio);
            $nh    = (int) round($h * $ratio);

            $dst = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

            ob_start();
            imagejpeg($dst, null, 85);
            $resized = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            return 'data:image/jpeg;base64,' . base64_encode($resized);
        } catch (\Throwable) {
            return $dataUrl; // fallback: original
        }
    }
}
