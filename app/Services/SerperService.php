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
            \Log::info('Iniciando busca Serper', ['query' => $query]);
    
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiEndpoint, [
                'q' => $query,
                'gl' => 'br',
                'num' => 10,
                'type' => 'places',
                'search_type' => 'places',
                'hl' => 'pt-br'
            ]);
    
            if ($response->successful()) {
                $data = $response->json();
                
                // Verifica se há resultados de places
                if (!isset($data['places']) || empty($data['places'])) {
                    \Log::warning('Nenhum resultado encontrado', ['query' => $query]);
                    return [];
                }
    
                // Formata e processa os resultados
                $competitors = [];
                foreach ($data['places'] as $place) {
                    // Verifica se tem dados mínimos necessários
                    if (empty($place['title'])) {
                        continue;
                    }
    
                    $competitor = [
                        'title' => $place['title'],
                        'name' => $place['title'],
                        'location' => $place['address'] ?? '',
                        'address' => $place['address'] ?? '',
                        'rating' => floatval($place['rating'] ?? 0),
                        'reviews' => intval($place['reviewsCount'] ?? 0),
                        'phone' => $place['phoneNumber'] ?? '',
                        'website' => $place['website'] ?? '',
                        'image_url' => $place['thumbnailUrl'] ?? '',
                        'categories' => $place['categories'] ?? [],
                        'hours' => $place['hours'] ?? [],
                        'description' => $this->generateDescription($place),
                        'metrics' => [
                            'rating_score' => $this->calculateRatingScore($place),
                            'popularity_score' => $this->calculatePopularityScore($place),
                            'online_presence_score' => $this->calculateOnlinePresenceScore($place)
                        ]
                    ];
    
                    $competitors[] = $competitor;
                }
    
                \Log::info('Busca concluída com sucesso', [
                    'total_results' => count($competitors)
                ]);
    
                return $competitors;
            }
    
            throw new \Exception('Erro na busca: ' . $response->body());
        } catch (\Exception $e) {
            \Log::error('Erro na busca Serper', [
                'erro' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }
    
    private function generateDescription($place)
{
    $description = [];

    if (!empty($place['categories'])) {
        $description[] = implode(', ', $place['categories']);
    }

    if (!empty($place['address'])) {
        $description[] = "Localizado em " . $place['address'];
    }

    if (!empty($place['rating'])) {
        $description[] = sprintf(
            "Avaliação média de %.1f estrelas baseada em %d avaliações",
            $place['rating'],
            $place['reviewsCount'] ?? 0
        );
    }

    return implode('. ', $description);
}
    
private function calculateRatingScore($place)
{
    $score = 5; // Base score
    
    if (!empty($place['rating'])) {
        $score += min(($place['rating'] * 2), 5);
    }
    
    if (!empty($place['reviewsCount'])) {
        $score += min(($place['reviewsCount'] / 100), 5);
    }
    
    return min(10, round($score, 1));
}
    
private function calculatePopularityScore($place)
{
    $score = 5;
    
    if (!empty($place['reviewsCount'])) {
        $score += min(($place['reviewsCount'] / 50), 5);
    }
    
    return min(10, round($score, 1));
}

    
private function calculateOnlinePresenceScore($place)
{
    $score = 5;
    
    if (!empty($place['website'])) $score += 2;
    if (!empty($place['phoneNumber'])) $score += 1;
    if (!empty($place['thumbnailUrl'])) $score += 1;
    if (!empty($place['categories'])) $score += 1;
    
    return min(10, round($score, 1));
}
    
    private function formatPlacesResults($data)
{
    $results = [];
    
    if (isset($data['places'])) {
        foreach ($data['places'] as $place) {
            $results[] = [
                'title' => $place['title'] ?? '',
                'location' => $place['address'] ?? '',
                'snippet' => $this->formatPlaceSnippet($place),
                'rating' => $place['rating'] ?? null,
                'reviews' => $place['reviewsCount'] ?? null,
                'phone' => $place['phone'] ?? '',
                'website' => $place['website'] ?? '',
                'image_url' => $place['thumbnailUrl'] ?? null,
                'serper_image' => $place['thumbnailUrl'] ?? null, 
                'coordinates' => [
                    'lat' => $place['latitude'] ?? null,
                    'lng' => $place['longitude'] ?? null
                ]
            ];
        }
    }
    
    return $results;
}
    
    private function formatPlaceSnippet($place)
    {
        $parts = [];
        
        if (!empty($place['address'])) {
            $parts[] = $place['address'];
        }
        
        if (!empty($place['rating'])) {
            $parts[] = "Avaliação: {$place['rating']}/5";
        }
        
        if (!empty($place['reviewsCount'])) {
            $parts[] = "{$place['reviewsCount']} avaliações";
        }
        
        return implode(' • ', $parts);
    }

    
    private function calculateCompetitorScore($place)
    {
        $score = 0;
        
        // Pontuação baseada na avaliação
        if (isset($place['rating'])) {
            $score += ($place['rating'] * 2); // Máximo de 10 pontos
        }
        
        // Pontuação baseada no número de avaliações
        if (isset($place['reviews'])) {
            $score += min(($place['reviews'] / 100), 5); // Máximo de 5 pontos
        }
        
        // Normaliza o score para escala de 1-10
        $score = max(1, min(10, round($score)));
        
        return $score;
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

public function searchKeywords($business)
{
    try {
        // Constrói a query baseada nos detalhes do negócio
        $query = "{$business->segment} {$business->city} {$business->state}";
        
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiEndpoint, [
            'q' => $query,
            'num' => 100 // Número de resultados
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $this->extractKeywords($data);
        }

        Log::warning('Serper API request failed', [
            'status' => $response->status(),
            'response' => $response->json()
        ]);

        return [];
    } catch (\Exception $e) {
        Log::error('Error searching keywords: ' . $e->getMessage());
        return [];
    }
}

private function extractKeywords($data)
{
    $keywords = [];
    
    if (isset($data['organic'])) {
        foreach ($data['organic'] as $result) {
            // Extrai palavras-chave do título
            if (isset($result['title'])) {
                $words = explode(' ', strtolower($result['title']));
                foreach ($words as $word) {
                    $word = trim($word);
                    if (strlen($word) > 3) {
                        $keywords[$word] = isset($keywords[$word]) ? $keywords[$word] + 1 : 1;
                    }
                }
            }
            
            // Extrai palavras-chave da descrição
            if (isset($result['snippet'])) {
                $words = explode(' ', strtolower($result['snippet']));
                foreach ($words as $word) {
                    $word = trim($word);
                    if (strlen($word) > 3) {
                        $keywords[$word] = isset($keywords[$word]) ? $keywords[$word] + 1 : 1;
                    }
                }
            }
        }
    }
    
    // Ordena por frequência e pega os 20 mais relevantes
    arsort($keywords);
    return array_slice($keywords, 0, 20, true);
}

public function searchCompetitors($businessName, $city, $coordinates = null)
{
    try {
        \Log::info("Iniciando busca de concorrentes", [
            'negocio' => $businessName,
            'cidade' => $city,
            'coordenadas' => $coordinates
        ]);

        // Validação dos parâmetros necessários
        if (empty($this->apiKey)) {
            throw new \Exception('API Key do Serper não configurada');
        }

        // Construção da query de busca
        $query = sprintf(
            '%s %s em %s concorrentes locais',
            $businessName,
            'estabelecimentos similares',
            $city
        );

        // Faz a requisição para a API do Serper
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiEndpoint, [
            'q' => $query,
            'gl' => 'br',
            'num' => 20,
            'type' => 'places',
            'search_type' => 'places',
            'hl' => 'pt-br'
        ]);

        if (!$response->successful()) {
            \Log::error('Falha na requisição à API do Serper', [
                'status' => $response->status(),
                'erro' => $response->body()
            ]);
            return [];
        }

        $data = $response->json();
        $competitors = [];

        if (isset($data['places'])) {
            foreach ($data['places'] as $place) {
                // Pula se for o mesmo negócio
                if ($this->isSameBusiness($place['title'], $businessName)) {
                    continue;
                }

                // Processa e valida a URL da imagem
                $imageUrl = $this->processImageUrl($place);

                $competitor = [
                    'title' => $place['title'] ?? '',
                    'name' => $place['title'] ?? '',
                    'location' => $place['address'] ?? '',
                    'address' => $place['address'] ?? '',
                    'rating' => floatval($place['rating'] ?? 0),
                    'reviews' => intval($place['reviewsCount'] ?? 0),
                    'phone' => $place['phoneNumber'] ?? '',
                    'website' => $place['website'] ?? '',
                    'image_url' => $imageUrl,
                    'thumbnailUrl' => $imageUrl,
                    'thumbnail' => $imageUrl,
                    'serper_image' => $imageUrl,
                    'photo' => $imageUrl,
                    'categories' => $place['categories'] ?? [],
                    'hours' => $place['hours'] ?? [],
                    'description' => $this->generateDescription($place),
                    'metrics' => [
                        'rating_score' => $this->calculateRatingScore($place),
                        'popularity_score' => $this->calculatePopularityScore($place),
                        'online_presence_score' => $this->calculateOnlinePresenceScore($place)
                    ],
                    'status' => 'active'
                ];

                $competitors[] = $competitor;
            }

            // Ordenação por relevância
            $competitors = $this->sortCompetitors($competitors);
        }

        \Log::info('Busca de concorrentes concluída', [
            'total_encontrados' => count($competitors)
        ]);

        return $competitors;

    } catch (\Exception $e) {
        \Log::error('Erro na busca de concorrentes', [
            'mensagem' => $e->getMessage(),
            'negocio' => $businessName,
            'cidade' => $city
        ]);
        return [];
    }
}

// Funções auxiliares necessárias

private function getPlaceDetails($placeId)
{
    try {
        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'formatted_address,formatted_phone_number,website,opening_hours,price_level',
            'language' => 'pt-BR',
            'key' => $this->apiKey
        ]);

        if ($response->successful()) {
            return $response->json()['result'] ?? [];
        }
        return [];
    } catch (\Exception $e) {
        \Log::error('Erro ao obter detalhes do local', ['place_id' => $placeId]);
        return [];
    }
}

private function getPlacePhoto($photoReference, $maxWidth = 400)
{
    if (!$photoReference) {
        return null;
    }

    return sprintf(
        'https://maps.googleapis.com/maps/api/place/photo?maxwidth=%d&photo_reference=%s&key=%s',
        $maxWidth,
        $photoReference,
        $this->apiKey
    );
}

private function calculateMetrics($place, $details)
{
    return [
        'rating_score' => $this->calculateRatingScore($place),
        'popularity_score' => $this->calculatePopularityScore($place),
        'online_presence_score' => $this->calculateOnlinePresenceScore($details),
        'price_level' => $details['price_level'] ?? 0
    ];
}

private function extractCategories($types)
{
    $categoryMapping = [
        'restaurant' => 'Restaurante',
        'cafe' => 'Café',
        'store' => 'Loja',
        // Adicione mais mapeamentos conforme necessário
    ];

    return array_map(function($type) use ($categoryMapping) {
        return $categoryMapping[$type] ?? ucfirst($type);
    }, $types);
}

private function sortCompetitors($competitors)
{
    usort($competitors, function($a, $b) {
        $scoreA = ($a['metrics']['rating_score'] * 0.4) + 
                 ($a['metrics']['popularity_score'] * 0.4) + 
                 ($a['metrics']['online_presence_score'] * 0.2);
                 
        $scoreB = ($b['metrics']['rating_score'] * 0.4) + 
                 ($b['metrics']['popularity_score'] * 0.4) + 
                 ($b['metrics']['online_presence_score'] * 0.2);
                 
        return $scoreB <=> $scoreA;
    });

    return array_slice($competitors, 0, 10); // Retorna apenas os 10 mais relevantes
}

private function isSameBusiness($name1, $name2)
{
    return similar_text(
        strtolower(trim($name1)),
        strtolower(trim($name2))
    ) > 80;
}

private function geocodeAddress($address)
{
    try {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => config('services.google.maps_api_key') // Certifique-se de ter configurado esta chave
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['results'][0]['geometry']['location'])) {
                return $data['results'][0]['geometry']['location'];
            }
        }

        \Log::error('Falha ao geocodificar endereço', [
            'address' => $address,
            'response' => $response->json()
        ]);

        return null;

    } catch (\Exception $e) {
        \Log::error('Erro ao geocodificar endereço: ' . $e->getMessage(), [
            'address' => $address
        ]);
        return null;
    }
}

// Função auxiliar para processar e validar URLs de imagem
private function processImageUrl($place)
{
    if (!empty($place['thumbnailUrl'])) {
        return $place['thumbnailUrl'];
    }
    
    if (!empty($place['photos'][0]['url'])) {
        return $place['photos'][0]['url'];
    }

    return null;
}

// Função auxiliar para validar URLs de imagem
private function isValidImageUrl($url)
{
    if (empty($url)) {
        return false;
    }

    // Verifica se a URL é válida
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    // Verifica se a URL termina com uma extensão de imagem comum
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $urlPath = parse_url($url, PHP_URL_PATH);
    $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));

    return in_array($extension, $imageExtensions);
}

public function searchSpecificCompetitor($name, $address)
{
    try {
        $query = sprintf('"%s" "%s"', $name, $address);
        
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiEndpoint, [
            'q' => $query,
            'gl' => 'br',
            'hl' => 'pt-br',
            'type' => 'places'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $places = $data['places'] ?? [];
            
            // Retorna apenas o primeiro resultado mais relevante
            return !empty($places) ? $places[0] : [];
        }

        return [];
    } catch (\Exception $e) {
        \Log::error('Erro na busca específica do Serper: ' . $e->getMessage());
        throw $e;
    }
}

}