<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SerperService
{
    protected $apiKey;
    protected $apiEndpoint = 'https://google.serper.dev/search';

    public function __construct()
    {
        $this->apiKey = config('services.serper.key'); // Você precisará adicionar isso
    }

    public function search($query)
    {
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiEndpoint, [
                'q' => $query,
                'gl' => 'br' // Localização Brasil
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro na busca Serper: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Erro ao fazer busca: ' . $e->getMessage());
            return null;
        }
    }
}