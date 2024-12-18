<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    protected $apiKey;
    protected $baseUrl = 'https://maps.googleapis.com/maps/api/place';
    protected $cacheTime = 3600; // 1 hora

    public function __construct()
    {
        $this->apiKey = config('services.google.places_api_key');
    }

    public function getNearbyCompetitors($params)
{
    try {
        $places = $this->searchNearbyPlaces($params);
        
        $competitors = [];
        foreach ($places as $place) {
            // Buscar detalhes adicionais do lugar
            $details = $this->getPlaceDetails($place['place_id']);
            
            $competitor = [
                'place_id' => $place['place_id'],
                'name' => $place['name'],
                'address' => $place['vicinity'] ?? $details['formatted_address'] ?? null,
                'rating' => $place['rating'] ?? null,
                'total_ratings' => $place['user_ratings_total'] ?? null,
                'distance' => $this->calculateDistance(
                    $params['location']['lat'],
                    $params['location']['lng'],
                    $place['geometry']['location']['lat'],
                    $place['geometry']['location']['lng']
                ),
                'phone' => $details['formatted_phone_number'] ?? null,
                'website' => $details['website'] ?? null,
                'photos' => !empty($place['photos']) ? array_map(function($photo) {
                    return $this->getPlacePhotoUrl($photo['photo_reference']);
                }, $place['photos']) : [],
                'segment' => !empty($place['types']) ? $place['types'][0] : null
            ];
            
            $competitors[] = $competitor;
        }

        return $competitors;
    } catch (\Exception $e) {
        \Log::error('Erro ao buscar competidores:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return [];
    }
}

// Novo método para verificar se o estabelecimento corresponde ao segmento
private function matchesSegment($placeTypes, $segment)
{
    // Mapeamento de segmentos para tipos do Google Places
    $segmentTypeMapping = [
        'restaurante' => ['restaurant', 'food'],
        'bar' => ['bar', 'night_club'],
        'cafe' => ['cafe', 'bakery'],
        // Adicione mais mapeamentos conforme necessário
    ];

    // Normaliza o segmento para minúsculas
    $segment = strtolower($segment);

    // Verifica se o segmento existe no mapeamento
    if (isset($segmentTypeMapping[$segment])) {
        // Verifica se algum dos tipos do lugar corresponde aos tipos mapeados para o segmento
        return !empty(array_intersect($placeTypes, $segmentTypeMapping[$segment]));
    }

    // Se não houver mapeamento específico, usa comparação direta
    return in_array(strtolower($segment), $placeTypes);
}


private function searchNearbyPlaces($params)
{
    try {
        $cacheKey = 'places_nearby_' . md5(json_encode($params));
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($params) {
            // Construir os parâmetros da requisição
            $queryParams = [
                'location' => $params['location']['lat'] . ',' . $params['location']['lng'],
                'radius' => $params['radius'] ?? 5000,
                'type' => 'establishment', // Adiciona um tipo genérico
                'keyword' => $params['segment'] ?? '',
                'language' => 'pt-BR',
                'key' => $this->apiKey
            ];

            // Log para debug
            \Log::info('Parâmetros da busca:', [
                'params' => array_merge($queryParams, ['key' => '***'])
            ]);

            // Fazer a requisição para a API
            $response = Http::get("{$this->baseUrl}/nearbysearch/json", $queryParams);
            
            // Log da resposta
            \Log::info('Resposta da API:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'OK' && !empty($data['results'])) {
                    return $data['results'];
                }
                
                \Log::warning('Sem resultados ou status não OK:', [
                    'status' => $data['status'],
                    'error_message' => $data['error_message'] ?? 'Sem mensagem de erro'
                ]);
            }

            return [];
        });
    } catch (\Exception $e) {
        \Log::error('Erro na busca de lugares:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return [];
    }
}

    private function getPlaceDetails($placeId)
    {
        try {
            $cacheKey = 'place_details_' . $placeId;
            
            return Cache::remember($cacheKey, $this->cacheTime, function () use ($placeId) {
                $response = Http::get("{$this->baseUrl}/details/json", [
                    'place_id' => $placeId,
                    'fields' => implode(',', [
                        'name',
                        'formatted_address',
                        'formatted_phone_number',
                        'website',
                        'rating',
                        'user_ratings_total',
                        'photos',
                        'opening_hours',
                        'geometry'
                    ]),
                    'language' => 'pt-BR',
                    'key' => $this->apiKey
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['result'] ?? [];
                }

                return [];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes do lugar: ' . $e->getMessage());
            return [];
        }
    }

    private function getPlacePhotoUrl($photoReference, $maxWidth = 400)
    {
        if (empty($photoReference)) {
            return null;
        }

        return "{$this->baseUrl}/photo?" . http_build_query([
            'maxwidth' => $maxWidth,
            'photo_reference' => $photoReference,
            'key' => $this->apiKey
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Raio da Terra em km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return round($earthRadius * $c, 2);
    }

    /**
 * Get place autocomplete suggestions
 * 
 * @param string $input The user's input to search for
 * @param array|null $location Optional location bias coordinates
 * @return array Array of autocomplete suggestions
 */
public function autocomplete($input, $location = null)
{
    try {
        $params = [
            'input' => $input,
            'key' => $this->apiKey,
            'types' => 'establishment|geocode',
            'language' => 'pt-BR'
        ];

        // Add location bias if coordinates are provided
        if ($location) {
            $params['location'] = "{$location['lat']},{$location['lng']}";
            $params['radius'] = 50000; // 50km radius
        }

        // Generate cache key based on parameters
        $cacheKey = 'places_autocomplete_' . md5(json_encode($params));

        // Try to get from cache first
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($params) {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', $params);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'OK') {
                    return array_map(function ($prediction) {
                        return [
                            'place_id' => $prediction['place_id'],
                            'description' => $prediction['description'],
                            'structured_formatting' => $prediction['structured_formatting'] ?? null,
                            'types' => $prediction['types'] ?? []
                        ];
                    }, $data['predictions']);
                }
            }
            
            return [];
        });
    } catch (\Exception $e) {
        Log::error('Error in place autocomplete: ' . $e->getMessage());
        return [];
    }
}
}