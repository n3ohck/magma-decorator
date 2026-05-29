<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ReplicateService
{
    private const BASE_URL = 'https://api.replicate.com/v1';

    private string $token;

    public function __construct()
    {
        $this->token = config('services.replicate.token') ?? '';
    }

    /**
     * Creates a prediction using a specific model version hash.
     * This is the most reliable endpoint — works for all public models.
     *
     * @param  string  $version  Full version SHA from replicate.com/model/versions
     * @param  array   $input    Model-specific input parameters
     */
    public function createPrediction(string $version, array $input): array
    {
        $response = Http::withToken($this->token)
            ->timeout(15)
            ->post(self::BASE_URL . '/predictions', [
                'version' => $version,
                'input'   => $input,
            ]);

        // Retry once on 429 using the retry_after hint from Replicate
        if ($response->status() === 429) {
            $retryAfter = $response->json('retry_after') ?? 5;
            Log::info("Replicate 429 — retrying after {$retryAfter}s");
            sleep((int) ceil($retryAfter));

            $response = Http::withToken($this->token)
                ->timeout(15)
                ->post(self::BASE_URL . '/predictions', [
                    'version' => $version,
                    'input'   => $input,
                ]);
        }

        if (! $response->successful()) {
            Log::error('Replicate createPrediction failed', [
                'version' => $version,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);

            $detail = $response->json('detail') ?? $response->body();
            throw new RuntimeException($detail, $response->status());
        }

        return $response->json();
    }

    /**
     * Fetches the current state of a prediction.
     */
    public function getPrediction(string $id): array
    {
        $response = Http::withToken($this->token)
            ->timeout(10)
            ->get(self::BASE_URL . "/predictions/{$id}");

        if (! $response->successful()) {
            throw new RuntimeException("Replicate getPrediction error ({$response->status()})");
        }

        return $response->json();
    }

    /**
     * Blocks until the prediction reaches a terminal state or the timeout is reached.
     * Polls every 2 seconds. Returns the final prediction object.
     */
    public function waitForResult(string $id, int $maxSeconds = 90): array
    {
        $deadline = time() + $maxSeconds;

        while (time() < $deadline) {
            $prediction = $this->getPrediction($id);

            if (in_array($prediction['status'], ['succeeded', 'failed', 'canceled'])) {
                return $prediction;
            }

            sleep(2);
        }

        return [
            'id'     => $id,
            'status' => 'timeout',
            'error'  => "Prediction did not complete within {$maxSeconds} seconds.",
        ];
    }

    /**
     * Returns true if a token is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->token);
    }
}
