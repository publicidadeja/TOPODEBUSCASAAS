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
        $parts = [];
        
        if (!empty($place['address'])) {
            $parts[] = "Localizado em {$place['address']}";
        }
        
        if (!empty($place['rating'])) {
            $parts[] = "Avaliação de {$place['rating']}/5";
        }
        
        if (!empty($place['reviewsCount'])) {
            $parts[] = "com {$place['reviewsCount']} avaliações";
        }
        
        if (!empty($place['categories'])) {
            $parts[] = "Categorias: " . implode(', ', $place['categories']);
        }
        
        return implode('. ', $parts);
    }
    
    private function calculateRatingScore($place)
    {
        $rating = floatval($place['rating'] ?? 0);
        $reviews = intval($place['reviewsCount'] ?? 0);
        
        // Pontuação base pela avaliação (0-50 pontos)
        $ratingScore = ($rating / 5) * 50;
        
        // Bônus por quantidade de reviews (0-50 pontos)
        $reviewScore = min(($reviews / 100) * 50, 50);
        
        return round(($ratingScore + $reviewScore) / 2);
    }
    
    private function calculatePopularityScore($place)
    {
        $score = 0;
        
        // Pontos por reviews
        $reviews = intval($place['reviewsCount'] ?? 0);
        if ($reviews > 0) {
            $score += min(($reviews / 100) * 60, 60);
        }
        
        // Pontos por presença de fotos
        if (!empty($place['thumbnailUrl'])) {
            $score += 20;
        }
        
        // Pontos por categorias definidas
        if (!empty($place['categories'])) {
            $score += 20;
        }
        
        return min($score, 100);
    }
    
    private function calculateOnlinePresenceScore($place)
    {
        $score = 0;
        
        // Website (40 pontos)
        if (!empty($place['website'])) {
            $score += 40;
        }
        
        // Telefone (20 pontos)
        if (!empty($place['phoneNumber'])) {
            $score += 20;
        }
        
        // Horários de funcionamento (20 pontos)
        if (!empty($place['hours'])) {
            $score += 20;
        }
        
        // Foto do local (20 pontos)
        if (!empty($place['thumbnailUrl'])) {
            $score += 20;
        }
        
        return $score;
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

public function searchCompetitors($businessName, $city)
{
    try {
        \Log::info("Searching competitors for: {$businessName} in {$city}");
        
        $query = sprintf(
            '%s %s em %s',
            $businessName,
            'concorrentes locais de google meu negocio próximos',
            $city
        );
        
        // Make the API request with enhanced parameters
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
            \Log::error('Serper API request failed: ' . $response->body());
            return [];
        }

        $data = $response->json();
        
        // Log da resposta completa para debug
        \Log::debug('Serper API Response:', $data);
        
        $competitors = [];
        if (isset($data['places'])) {
            foreach ($data['places'] as $place) {
                // Skip if it's the same business
                if (strtolower($place['title']) === strtolower($businessName)) {
                    continue;
                }

                // Log dos dados da imagem para debug
                \Log::debug('Place Image Data:', [
                    'title' => $place['title'],
                    'thumbnailUrl' => $place['thumbnailUrl'] ?? null,
                    'photos' => $place['photos'] ?? null,
                    'imageUrl' => $place['imageUrl'] ?? null
                ]);

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

            // Ordena por relevância
            usort($competitors, function($a, $b) {
                $scoreA = ($a['rating'] * 2) + ($a['reviews'] / 100);
                $scoreB = ($b['rating'] * 2) + ($b['reviews'] / 100);
                return $scoreB <=> $scoreA;
            });

            // Limita aos 10 mais relevantes
            $competitors = array_slice($competitors, 0, 10);
        }

        \Log::info('Competitor search completed', [
            'query' => $query,
            'total_found' => count($competitors)
        ]);

        return $competitors;

    } catch (\Exception $e) {
        \Log::error('Error searching competitors: ' . $e->getMessage(), [
            'businessName' => $businessName,
            'city' => $city
        ]);
        return [];
    }
}

// Função auxiliar para processar e validar URLs de imagem
private function processImageUrl($place)
{
    try {
        // Tenta obter a URL da imagem de diferentes campos possíveis
        $imageUrl = null;

        // Prioridade 1: thumbnailUrl
        if (!empty($place['thumbnailUrl'])) {
            $imageUrl = $place['thumbnailUrl'];
        }
        // Prioridade 2: photos array
        elseif (!empty($place['photos']) && is_array($place['photos'])) {
            $imageUrl = $place['photos'][0] ?? null;
        }
        // Prioridade 3: imageUrl
        elseif (!empty($place['imageUrl'])) {
            $imageUrl = $place['imageUrl'];
        }

        // Se não encontrou nenhuma imagem, retorna uma imagem padrão
        if (!$imageUrl) {
            return asset('images/default-business.png'); // Crie uma imagem padrão
        }

        // Remove possíveis parâmetros de URL que possam invalidar a imagem
        $imageUrl = strtok($imageUrl, '?');

        // Verifica se a URL começa com http ou https
        if (!preg_match("~^(?:f|ht)tps?://~i", $imageUrl)) {
            $imageUrl = 'https://' . ltrim($imageUrl, '/');
        }

        return $imageUrl;

    } catch (\Exception $e) {
        \Log::error('Error processing image URL: ' . $e->getMessage());
        return asset('images/default-business.png');
    }
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


}