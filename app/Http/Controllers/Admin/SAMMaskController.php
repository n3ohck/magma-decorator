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
            'image_url'    => ['required', 'url'],
            'x'            => ['required', 'numeric', 'min:0', 'max:1'],
            'y'            => ['required', 'numeric', 'min:0', 'max:1'],
            'image_width'  => ['required', 'integer', 'min:1'],
            'image_height' => ['required', 'integer', 'min:1'],
        ]);

        // Convert relative coords to absolute pixel coords
        $pixelX = (int) round($data['x'] * $data['image_width']);
        $pixelY = (int) round($data['y'] * $data['image_height']);

        // Create SAM 2 prediction
        // Model: meta/sam-2 (https://replicate.com/meta/sam-2)
        // Input keys may vary slightly by model version — update if the model
        // changes its API. The standard SAM 2 on Replicate accepts:
        //   image, input_points, input_labels, multimask_output
        // meta/sam-2 version hash — https://replicate.com/meta/sam-2/versions
        $version = config(
            'services.replicate.sam_version',
            'fe97b453a6455861e3bac769b441ca1f1086110da7466dbb65cf1eecfd60dc83'
        );

        $prediction = $replicate->createPrediction($version, [
            'image'            => $data['image_url'],
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
}
