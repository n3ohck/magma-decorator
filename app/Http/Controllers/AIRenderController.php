<?php

namespace App\Http\Controllers;

use App\Services\ReplicateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIRenderController extends Controller
{
    /**
     * POST /decorador/ai-render
     *
     * Accepts a base64 PNG of the current canvas composite, saves it temporarily,
     * starts a Replicate SDXL img2img prediction and returns the prediction ID for
     * the client to poll.
     *
     * Note: Replicate needs a publicly accessible URL for the input image.
     * In local development make sure APP_URL points to a publicly reachable
     * address (e.g. via ngrok) or the request will fail.
     */
    public function create(Request $request, ReplicateService $replicate): JsonResponse
    {
        if (! $replicate->isConfigured()) {
            return response()->json(['error' => 'Replicate API token no configurado.'], 503);
        }

        $request->validate([
            'image_data' => ['required', 'string'],   // data:image/png;base64,...
            'materials'  => ['nullable', 'array'],
        ]);

        $dataUrl = $request->string('image_data')->toString();

        if (! str_starts_with($dataUrl, 'data:image/')) {
            return response()->json(['error' => 'Formato de imagen inválido.'], 422);
        }

        // Pass the data URI directly to Replicate — no public URL needed.
        // Real-ESRGAN (and most Replicate models) accept base64 data URIs,
        // which avoids the need for ngrok or a public server in development.
        $imageUrl = $dataUrl;

        // nightmareai/real-esrgan — upscale + AI enhancement
        // Sharpens and adds photorealistic detail to the composite without
        // changing the composition. Fast (~10-15 s), no prompt needed.
        // Version: https://replicate.com/nightmareai/real-esrgan/versions
        $version = config(
            'services.replicate.render_version',
            '42fed1c4974146d4d2414e2be2c5277c7fcf05fcc3a73abf41610695738c1d7b'
        );

        try {
            $prediction = $replicate->createPrediction($version, [
                'image'        => $imageUrl,
                'scale'        => 2,
                'face_enhance' => false,
            ]);
        } catch (\RuntimeException $e) {

            $code = $e->getCode();
            $msg  = match (true) {
                $code === 429 => 'Límite de peticiones alcanzado en Replicate. Agrega un método de pago en replicate.com o intenta en unos segundos.',
                $code === 401 => 'Token de Replicate inválido. Verifica REPLICATE_API_TOKEN en .env.',
                default       => 'Error al conectar con Replicate: ' . $e->getMessage(),
            };

            return response()->json(['error' => $msg], 422);
        }

        return response()->json([
            'prediction_id' => $prediction['id'],
            'status'        => $prediction['status'],
        ]);
    }

    /**
     * GET /decorador/ai-render/{id}/status
     *
     * Polls the prediction state. When succeeded, returns the output URL.
     */
    public function status(string $id, ReplicateService $replicate): JsonResponse
    {
        if (! $replicate->isConfigured()) {
            return response()->json(['error' => 'Replicate API token no configurado.'], 503);
        }

        $prediction = $replicate->getPrediction($id);

        $outputUrl = null;

        if ($prediction['status'] === 'succeeded') {
            $output    = $prediction['output'];
            $outputUrl = is_array($output) ? $output[0] : $output;
        }

        return response()->json([
            'status'     => $prediction['status'],
            'output_url' => $outputUrl,
            'error'      => $prediction['error'] ?? null,
        ]);
    }
}
