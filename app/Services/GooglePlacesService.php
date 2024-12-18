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
                
                // Calcular distância
                $distance = $this->calculateDistance(
                    $params['location']['lat'],
                    $params['location']['lng'],
                    $place['geometry']['location']['lat'],
                    $place['geometry']['location']['lng']
                );
    
                // Processar fotos
                $photos = !empty($place['photos']) ? array_map(function($photo) {
                    return $this->getPlacePhotoUrl($photo['photo_reference']);
                }, array_slice($place['photos'], 0, 5)) : [];
    
                // Processar reviews
                $reviews = isset($details['reviews']) ? array_map(function($review) {
                    return [
                        'author_name' => $review['author_name'] ?? null,
                        'rating' => $review['rating'] ?? null,
                        'text' => $review['text'] ?? null,
                        'time' => $review['time'] ?? null,
                        'profile_photo_url' => $review['profile_photo_url'] ?? null,
                        'relative_time_description' => $review['relative_time_description'] ?? null
                    ];
                }, array_slice($details['reviews'], 0, 2)) : [];
    
                $competitor = [
                    // Informações básicas
                    'place_id' => $place['place_id'],
                    'name' => $place['name'],
                    'address' => $place['vicinity'] ?? $details['formatted_address'] ?? null,
                    'rating' => $place['rating'] ?? null,
                    'total_ratings' => $place['user_ratings_total'] ?? null,
                    'distance' => $distance,
                    'status' => $place['business_status'] ?? 'UNKNOWN',
                    'price_level' => $place['price_level'] ?? null,
    
                    // Contato
                    'phone' => $details['formatted_phone_number'] ?? null,
                    'international_phone' => $details['international_phone_number'] ?? null,
                    'website' => $details['website'] ?? null,
                    'url' => $details['url'] ?? null,
    
                    // Horários e status
                    'hours' => $details['opening_hours']['weekday_text'] ?? null,
                    'open_now' => $details['opening_hours']['open_now'] ?? null,
                    'periods' => $details['opening_hours']['periods'] ?? null,
    
                    // Mídia
                    'photos' => $photos,
                    'reviews' => $reviews,
    
                    // Localização
                    'location' => [
                        'lat' => $place['geometry']['location']['lat'],
                        'lng' => $place['geometry']['location']['lng']
                    ],
                    'plus_code' => $details['plus_code'] ?? null,
                    'utc_offset' => $details['utc_offset'] ?? null,
    
                    // Características do estabelecimento
                    'types' => $place['types'] ?? [],
                    'wheelchair_accessible' => $details['wheelchair_accessible_entrance'] ?? false,
                    'delivery' => $details['delivery'] ?? false,
                    'dine_in' => $details['dine_in'] ?? false,
                    'takeout' => $details['takeout'] ?? false,
                    'serves_breakfast' => $details['serves_breakfast'] ?? false,
                    'serves_lunch' => $details['serves_lunch'] ?? false,
                    'serves_dinner' => $details['serves_dinner'] ?? false,
                    'outdoor_seating' => $details['outdoor_seating'] ?? false,
                    'reservable' => $details['reservable'] ?? false,
    
                    // Links sociais
                    'social_links' => [
                        'google_page' => $details['url'] ?? null,
                        'facebook' => $this->extractSocialLink($details, 'facebook'),
                        'instagram' => $this->extractSocialLink($details, 'instagram'),
                        'twitter' => $this->extractSocialLink($details, 'twitter')
                    ],
    
                    // Métricas adicionais
                    'popularity' => $this->calculatePopularityScore([
                        'rating' => $place['rating'] ?? 0,
                        'total_ratings' => $place['user_ratings_total'] ?? 0,
                        'distance' => $distance
                    ])
                ];
                
                $competitors[] = $competitor;
            }
    
            // Ordenar por relevância (opcional)
            usort($competitors, function($a, $b) {
                return $b['popularity'] <=> $a['popularity'];
            });
    
            return $competitors;
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar competidores:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'params' => $params
            ]);
            return [];
        }
    }
    
    // Método auxiliar para calcular score de popularidade
    private function calculatePopularityScore($metrics)
    {
        $rating = $metrics['rating'] ?? 0;
        $totalRatings = $metrics['total_ratings'] ?? 0;
        $distance = $metrics['distance'] ?? 5000; // valor padrão de 5km
    
        // Normalizar distância (quanto mais próximo, melhor)
        $distanceScore = max(0, 1 - ($distance / 5000));
    
        // Calcular score baseado em rating e número de avaliações
        $ratingScore = ($rating * log10($totalRatings + 1)) / 5;
    
        // Combinar os scores (70% rating/reviews, 30% distância)
        return ($ratingScore * 0.7) + ($distanceScore * 0.3);
    }
    
    // Método auxiliar para extrair links sociais
    private function extractSocialLink($details, $platform)
    {
        if (empty($details['website'])) {
            return null;
        }
    
        $socialLinks = [
            'facebook' => '/facebook.com/',
            'instagram' => '/instagram.com/',
            'twitter' => '/twitter.com/'
        ];
    
        $pattern = $socialLinks[$platform] ?? null;
        if (!$pattern) {
            return null;
        }
    
        $urls = preg_grep($pattern, (array)$details['website']);
        return !empty($urls) ? reset($urls) : null;
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
                    'international_phone_number',
                    'website',
                    'url',
                    'rating',
                    'user_ratings_total',
                    'reviews',
                    'photos',
                    'opening_hours',
                    'business_status',
                    'price_level',
                    'geometry',
                    'types',
                    'plus_code',
                    'utc_offset',
                    'wheelchair_accessible_entrance',
                    'delivery',
                    'dine_in',
                    'takeout',
                    'serves_breakfast',
                    'serves_lunch',
                    'serves_dinner',
                    'outdoor_seating',
                    'reservable'
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