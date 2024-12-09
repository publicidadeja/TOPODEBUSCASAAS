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
            'gl' => 'br',
            'num' => 10, // Número de resultados
            'type' => 'search'
        ]);

        if ($response->successful()) {
            return $this->formatResults($response->json());
        }

        Log::error('Erro na busca Serper: ' . $response->body());
        return null;

    } catch (\Exception $e) {
        Log::error('Erro ao fazer busca: ' . $e->getMessage());
        return null;
    }
}

private function formatResults($data)
{
    $results = [];
    
    if (isset($data['organic'])) {
        foreach ($data['organic'] as $item) {
            $results[] = [
                'title' => $item['title'] ?? '',
                'link' => $item['link'] ?? '',
                'snippet' => $item['snippet'] ?? '',
                'position' => $item['position'] ?? 0
            ];
        }
    }
    
    return $results;
}
}