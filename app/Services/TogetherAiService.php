<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TogetherAiService
{
    private $apiKey;
    private $baseUrl = 'https://api.together.xyz/v1';

    public function __construct()
    {
        $this->apiKey = config('services.together.api_key');
    }

    public function createEmbedding(string $text)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl . '/embeddings', [
                'model' => 'togethercomputer/m2-bert-80M-8k-retrieval',
                'input' => $text,
            ]);

            Log::info('Together.ai API Response:', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to create embedding: ' . $response->body());
            }

            return $response->json()['data'][0]['embedding'];
            
        } catch (\Exception $e) {
            Log::error('Together.ai API Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}