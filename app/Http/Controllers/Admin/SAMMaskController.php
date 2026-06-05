<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReplicateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        // Versión de SAM 2 en Replicate
        $version = config(
            'services.replicate.sam_version',
            'fe97b453a6455861e3bac769b441ca1f1086110da7466dbb65cf1eecfd60dc83'
        );

        $prediction = $replicate->createPrediction($version, [
            'image'            => $data['image_data'],  // base64 — no necesita URL pública
            'input_points'     => [[$pixelX, $pixelY]],
            'input_labels'     => [1],
            'multimask_output' => false,
        ]);

        // SAM is fast — wait synchronously (timeout 60s)
        $result = $replicate->waitForResult($prediction['id'], maxSeconds: 60);

        if ($result['status'] !== 'succeeded') {
            return response()->json([
                'error' => 'SAM no pudo generar la máscara: ' . ($result['error'] ?? $result['status']),
            ], 500);
        }

        // The output is a mask image URL (PNG with white = segment, black = background)
        $output  = $result['output'];
        $maskUrl = is_array($output) ? ($output[0] ?? null) : $output;

        if (! $maskUrl) {
            return response()->json(['error' => 'SAM no devolvió una imagen de máscara.'], 500);
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
}
