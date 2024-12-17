<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    protected $apiKey;
    protected $baseUrl = 'https://maps.googleapis.com/maps/api/place';
    protected $maxRequestsPerMinute = 60;
    protected $cacheTime = 3600; // 1 hora

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function nearbySearch($params)
    {
        try {
            $cacheKey = 'places_nearby_' . md5(json_encode($params));
            
            return Cache::remember($cacheKey, $this->cacheTime, function () use ($params) {
                $response = Http::get("{$this->baseUrl}/nearbysearch/json", [
                    'key' => $this->apiKey,
                    'location' => "{$params['lat']},{$params['lng']}",
                    'radius' => $params['radius'] ?? 1000,
                    'type' => $params['type'] ?? '',
                    'keyword' => $params['keyword'] ?? ''
                ]);

                Log::info('Google Places API Nearby Search', [
                    'params' => $params,
                    'status' => $response->status()
                ]);

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Erro na busca do Google Places', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getPlaceDetails($placeId)
    {
        try {
            $cacheKey = 'place_details_' . $placeId;
            
            return Cache::remember($cacheKey, $this->cacheTime, function () use ($placeId) {
                $response = Http::get("{$this->baseUrl}/details/json", [
                    'key' => $this->apiKey,
                    'place_id' => $placeId,
                    'fields' => 'name,rating,formatted_address,photos,opening_hours'
                ]);

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes do local', [
                'place_id' => $placeId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getPlacePhotos($photoReference)
    {
        try {
            return "{$this->baseUrl}/photo?maxwidth=400&photo_reference={$photoReference}&key={$this->apiKey}";
        } catch (\Exception $e) {
            Log::error('Erro ao buscar foto do local', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function autocomplete($input, $location = null)
    {
        try {
            $params = [
                'key' => $this->apiKey,
                'input' => $input,
                'language' => 'pt-BR'
            ];

            if ($location) {
                $params['location'] = "{$location['lat']},{$location['lng']}";
                $params['radius'] = $location['radius'] ?? 50000;
            }

            $response = Http::get("{$this->baseUrl}/autocomplete/json", $params);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Erro no autocomplete', [
                'input' => $input,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}