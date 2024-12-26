<?php


namespace App\Http\Controllers;

use App\Services\AIAnalysisService;
use App\Services\SerperService;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log; 

class CompetitorAnalysisController extends Controller
{
    protected $aiAnalysis;
    protected $serper;
    protected $gemini;

    public function __construct(AIAnalysisService $aiAnalysis, SerperService $serper, GeminiService $gemini)
    {
        $this->aiAnalysis = $aiAnalysis;
        $this->serper = $serper;
        $this->gemini = $gemini;
    }

    public function analyze(Request $request)
{
    try {
        // Validação dos dados
        $request->validate([
            'business_id' => 'required|exists:businesses,id'
        ]);

        $business = Business::findOrFail($request->business_id);
        
        // Busca os concorrentes usando o serviço Serper
        $competitors = $this->searchCompetitors($business);
        
        // Formata os dados dos concorrentes
        $formattedCompetitors = array_map(function($competitor) {
            return [
                'title' => $competitor['title'] ?? 'Nome não disponível',
                'name' => $competitor['title'] ?? 'Nome não disponível',
                'location' => $competitor['location'] ?? $competitor['address'] ?? 'Localização não disponível',
                'address' => $competitor['location'] ?? $competitor['address'] ?? 'Localização não disponível',
                'rating' => floatval($competitor['rating'] ?? 0),
                'reviews' => intval($competitor['reviews'] ?? 0),
                'phone' => $competitor['phone'] ?? null,
                'website' => $competitor['website'] ?? null,
                'image_url' => $competitor['thumbnailUrl'] ?? $competitor['image_url'] ?? null,
                'score' => $this->calculateCompetitorScore($competitor),
                'summary' => $competitor['snippet'] ?? 'Resumo não disponível',
            ];
        }, $competitors);

        // Gera análise de mercado
        $marketAnalysis = $this->generateMarketAnalysis($formattedCompetitors);
        
        // Gera recomendações usando o Gemini
        $recommendations = $this->generateRecommendations($formattedCompetitors);

        return response()->json([
            'success' => true,
            'competitors' => $formattedCompetitors,
            'marketAnalysis' => $marketAnalysis,
            'recommendations' => $recommendations
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro na análise de concorrentes: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao realizar análise: ' . $e->getMessage()
        ], 500);
    }
}
    
    private function calculateCompetitorScore($competitor)
    {
        $score = 5; // Score base
    
        // Aumenta score baseado na presença de informações
        if (!empty($competitor['rating'])) {
            $score += min(($competitor['rating'] / 2), 2.5); // Máximo de 2.5 pontos para rating
        }
        
        if (!empty($competitor['reviews'])) {
            $score += min(($competitor['reviews'] / 100), 1.5); // Máximo de 1.5 pontos para reviews
        }
    
        if (!empty($competitor['website'])) {
            $score += 0.5;
        }
    
        if (!empty($competitor['phone'])) {
            $score += 0.5;
        }
    
        return min(10, round($score, 1)); // Garante máximo de 10 pontos
    }
    
    private function generateMarketAnalysis($competitors)
    {
        $totalCompetitors = count($competitors);
        $avgRating = array_reduce($competitors, function($carry, $item) {
            return $carry + ($item['rating'] ?? 0);
        }, 0) / max(1, $totalCompetitors);
    
        return [
            [
                'title' => 'Visão Geral do Mercado',
                'description' => sprintf(
                    'Análise baseada em %d concorrentes principais. Média de avaliação do mercado: %.1f/5',
                    $totalCompetitors,
                    $avgRating
                ),
                'metrics' => [
                    [
                        'label' => 'Total de Concorrentes',
                        'value' => $totalCompetitors
                    ],
                    [
                        'label' => 'Média de Avaliação',
                        'value' => number_format($avgRating, 1) . '/5'
                    ]
                ]
            ]
        ];
    }
    
    
    private function parseGeminiRecommendations($analysis)
    {
        $recommendations = [];
        
        // Divide a resposta em linhas
        $lines = explode("\n", $analysis);
        
        foreach ($lines as $line) {
            // Procura por linhas que começam com números (1., 2., 3.)
            if (preg_match('/^\d+\.\s+(.+?):\s+(.+?)\s*\(prioridade:\s*(\w+)\)$/i', $line, $matches)) {
                $recommendations[] = [
                    'title' => trim($matches[1]),
                    'description' => trim($matches[2]),
                    'priority' => strtolower(trim($matches[3]))
                ];
            }
        }

        return $recommendations;
    }

    private function generateDefaultRecommendations($competitors)
    {
        $recommendations = [];
        
        // Código original das recomendações padrão
        $withWebsite = array_filter($competitors, fn($c) => !empty($c['website']));
        if (count($withWebsite) / count($competitors) > 0.7) {
            $recommendations[] = [
                'title' => 'Presença Online',
                'description' => 'A maioria dos concorrentes possui website. Considere investir em sua presença digital.',
                'priority' => 'high'
            ];
        }
    
        $avgRating = array_reduce($competitors, fn($carry, $c) => $carry + ($c['rating'] ?? 0), 0) / count($competitors);
        if ($avgRating > 4) {
            $recommendations[] = [
                'title' => 'Qualidade do Serviço',
                'description' => 'O mercado possui alto padrão de qualidade. Foque em diferenciais competitivos.',
                'priority' => 'medium'
            ];
        }
    
        if (count($recommendations) < 2) {
            $recommendations[] = [
                'title' => 'Análise de Mercado',
                'description' => 'Monitore regularmente as estratégias dos concorrentes para identificar oportunidades.',
                'priority' => 'low'
            ];
        }
    
        return $recommendations;
    }

private function searchCompetitors($business)
{
    \Log::info('Iniciando busca de concorrentes', [
        'business' => [
            'id' => $business->id,
            'segment' => $business->segment,
            'address' => $business->address,
            'city' => $business->city,
            'state' => $business->state
        ]
    ]);

    // Validação dos dados necessários
    if (empty($business->segment) || empty($business->city) || empty($business->state) || empty($business->address)) {
        \Log::warning('Dados do negócio incompletos', [
            'segment' => $business->segment,
            'address' => $business->address,
            'city' => $business->city,
            'state' => $business->state
        ]);
        throw new \Exception('Dados do negócio incompletos para busca de concorrentes');
    }

    // Construção da query mais específica incluindo endereço
    $query = sprintf(
        '%s próximo a %s, %s, %s',
        trim($business->segment),
        trim($business->address),
        trim($business->city),
        trim($business->state)
    );

    \Log::info('Executando busca', ['query' => $query]);
    
    try {
        $results = $this->serper->search($query);
        
        // Filtragem e formatação dos resultados
        $competitors = array_map(function($result) {
            return [
                'title' => $result['title'] ?? '',
                'location' => $result['location'] ?? '',
                'snippet' => $result['snippet'] ?? '',
                'rating' => $result['rating'] ?? null,
                'reviews' => $result['reviews'] ?? null,
                'phone' => $result['phone'] ?? '',
                'website' => $result['website'] ?? ''
            ];
        }, $results);

        // Filtra resultados irrelevantes e o próprio negócio
        $competitors = array_filter($competitors, function($competitor) use ($business) {
            // Remove resultados vazios ou muito curtos
            if (empty($competitor['title']) || strlen($competitor['title']) < 3) {
                return false;
            }

            // Remove o próprio negócio da lista
            if (stripos($competitor['title'], $business->name) !== false) {
                return false;
            }

            // Verifica se está na mesma cidade
            if (!empty($competitor['location']) && 
                stripos($competitor['location'], $business->city) === false) {
                return false;
            }

            return true;
        });

        \Log::info('Resultados processados', [
            'query' => $query,
            'total_results' => count($results),
            'filtered_results' => count($competitors)
        ]);

        return array_values($competitors); // Reindexar array

    } catch (\Exception $e) {
        \Log::error('Erro na busca Serper', [
            'error' => $e->getMessage(),
            'query' => $query
        ]);
        throw $e;
    }
}
// Funções auxiliares para melhorar a qualidade dos dados
private function cleanTitle($title)
{
    // Remove textos comuns que não são nomes de empresas
    $removeTexts = ['- Google Maps', '| Facebook', '| Instagram', 'Página inicial'];
    $title = str_replace($removeTexts, '', $title);
    
    // Limpa espaços extras
    return trim($title);
}

private function extractLocation($snippet)
{
    // Tenta extrair endereço do snippet
    if (preg_match('/(?:R\.|Rua|Av\.|Avenida|Al\.|Alameda).*?,.*?(?:\d{5}-\d{3}|\d{8})/', $snippet, $matches)) {
        return $matches[0];
    }
    
    return 'Localização não disponível';
}

private function calculateScore($result)
{
    $score = 5; // Score base
    
    // Aumenta score baseado em fatores relevantes
    if (stripos($result['title'], 'oficial') !== false) $score += 1;
    if (stripos($result['snippet'], 'desde') !== false) $score += 1;
    if (isset($result['position']) && $result['position'] <= 3) $score += 2;
    
    // Normaliza o score entre 1 e 10
    return max(1, min(10, $score));
}

private function isRelevantCompetitor($result, $business)
{
    // Verificar se é do mesmo segmento
    if (!isset($result['category']) || 
        !str_contains(strtolower($result['category']), strtolower($business->segment))) {
        return false;
    }

    // Verificar se tem dados do Google My Business
    if (!isset($result['googlePlace'])) {
        return false;
    }

    return true;
}

// CompetitorAnalysisController.php
public function analyzeSingle(Request $request)
{
    try {
        $competitor = $request->all();
        
        // Fix the competitor_data if it's malformed
        if (!empty($competitor['competitor_data'])) {
            if (is_array($competitor['competitor_data']) && isset($competitor['competitor_data'][0])) {
                // If the data is split into characters, join them back
                $jsonString = implode('', $competitor['competitor_data']);
                // Decode the JSON string
                $decodedData = json_decode($jsonString, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $competitor['competitor_data'] = $decodedData;
                }
            }
        }

        // Extract rating and total_reviews from competitor_data if available
        if (!empty($competitor['competitor_data'])) {
            $competitor['rating'] = $competitor['competitor_data']['rating'] ?? 0;
            $competitor['total_reviews'] = $competitor['competitor_data']['total_ratings'] ?? 0;
        }

        // Busca informações adicionais
        $keywords = $this->serper->getRankingKeywords($competitor);
        $socialMedia = $this->getSocialMediaPresence($competitor);
        
        // Adiciona as informações ao competitor
        $competitor['keywords'] = $keywords;
        $competitor['social_media'] = $socialMedia;

        // Construir prompt para análise
        $prompt = $this->buildAnalysisPrompt($competitor);
        
        // Usar o GeminiService para gerar a análise
        $analysis = $this->gemini->generateContent($prompt);

        // Processar a resposta do Gemini
        $processedAnalysis = $this->processGeminiResponse($analysis);

        // Calcular métricas
        $engagementRate = $this->calculateEngagementRate($competitor);
        $onlinePresenceScore = $this->calculateOnlinePresenceScore($competitor);
        $customerSatisfactionScore = $this->calculateCustomerSatisfactionScore($competitor);

        // Formatar a análise final
        $formattedAnalysis = [
            'overview' => $processedAnalysis['overview'] ?? 'Análise não disponível',
            'strengths' => $processedAnalysis['strengths'] ?? [],
            'opportunities' => $processedAnalysis['opportunities'] ?? [],
            'recommendations' => $processedAnalysis['recommendations'] ?? [],
            'metrics' => [
                'rating' => floatval($competitor['rating'] ?? 0),
                'reviews' => intval($competitor['total_reviews'] ?? 0),
                'engagement_rate' => $engagementRate,
                'online_presence_score' => $onlinePresenceScore,
                'customer_satisfaction_score' => $customerSatisfactionScore
            ],
            'presence' => [
                'has_website' => !empty($competitor['website']),
                'has_social_media' => !empty($socialMedia),
                'has_keywords' => !empty($keywords)
            ]
        ];

        return response()->json([
            'success' => true,
            'analysis' => $formattedAnalysis
        ]);

    } catch (\Exception $e) {
        Log::error('Erro na análise do concorrente: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao analisar concorrente: ' . $e->getMessage()
        ], 500);
    }
}

private function buildAnalysisPrompt($competitor)
{
    $prompt = "Analise detalhadamente o seguinte estabelecimento comercial:\n\n";
    $prompt .= "Nome: " . ($competitor['name'] ?? 'Não disponível') . "\n";
    $prompt .= "Endereço: " . ($competitor['address'] ?? 'Não disponível') . "\n";
    $prompt .= "Avaliação: " . number_format(floatval($competitor['rating'] ?? 0), 1) . "\n";
    $prompt .= "Total de Avaliações: " . intval($competitor['total_reviews'] ?? 0) . "\n";

    if (!empty($competitor['keywords'])) {
        $prompt .= "\nPalavras-chave principais:\n";
        foreach ($competitor['keywords'] as $keyword => $data) {
            if (is_array($data)) {
                $prompt .= "- $keyword\n";
            } else {
                $prompt .= "- $data\n";
            }
        }
    }

    if (!empty($competitor['social_media'])) {
        $prompt .= "\nPresença em Redes Sociais:\n";
        foreach ($competitor['social_media'] as $platform => $url) {
            if (is_string($url)) {
                $prompt .= "- $platform: $url\n";
            }
        }
    }

    $prompt .= "\nForneça uma análise completa incluindo:\n";
    $prompt .= "1. Resumo executivo do negócio\n";
    $prompt .= "2. Pontos fortes identificados\n";
    $prompt .= "3. Oportunidades de melhoria\n";
    $prompt .= "4. Recomendações estratégicas específicas\n";

    return $prompt;
}

private function generateComprehensiveAnalysisPrompt($data)
{
    return "Analise detalhadamente o seguinte concorrente:

Nome: {$data['basic_info']['name']}
Endereço: {$data['basic_info']['address']}

POSICIONAMENTO:
- Palavras-chave principais: " . implode(", ", $data['keywords']['main_keywords']) . "
- Proposta de valor: {$data['content']['value_proposition']}

PERFORMANCE:
- Avaliação média: {$data['basic_info']['rating']}
- Total de avaliações: {$data['basic_info']['reviews_count']}
- Desempenho em redes sociais: {$data['social_media']['summary']}

ANÁLISE DE PÚBLICO:
- Demografia principal: {$data['demographics']['primary_audience']}
- Interesses identificados: " . implode(", ", $data['demographics']['interests']) . "

Por favor, forneça:
1. Resumo executivo do posicionamento do concorrente
2. Análise detalhada dos pontos fortes e fracos
3. Oportunidades identificadas no mercado
4. Recomendações estratégicas para competir
5. Análise comparativa de mercado";
}

private function analyzeKeywords($competitor)
{
    return [
        'main_keywords' => $this->serper->extractKeywords($competitor),
        'search_volume' => $this->serper->getSearchVolume($competitor),
        'ranking_keywords' => $this->serper->getRankingKeywords($competitor)
    ];
}

private function analyzeSocialMedia($competitor)
{
    return [
        'platforms' => $this->getSocialMediaPresence($competitor),
        'engagement_metrics' => $this->getSocialMediaEngagement($competitor),
        'content_analysis' => $this->analyzeSocialContent($competitor),
        'summary' => $this->generateSocialMediaSummary($competitor)
    ];
}

private function getSocialMediaPresence($competitor)
{
    try {
        $socialMedia = [];
        
        // Verifica redes sociais já presentes nos dados
        if (isset($competitor['social_media'])) {
            return $competitor['social_media'];
        }

        // Extrai do website se disponível
        if (!empty($competitor['website'])) {
            $domain = parse_url($competitor['website'], PHP_URL_HOST);
            
            // Busca por links de redes sociais no website
            try {
                $response = Http::get($competitor['website']);
                $content = $response->body();
                
                // Procura por links de redes sociais
                $patterns = [
                    'facebook' => '/facebook\.com\/[a-zA-Z0-9\.]+/',
                    'instagram' => '/instagram\.com\/[a-zA-Z0-9\.]+/',
                    'linkedin' => '/linkedin\.com\/[a-zA-Z0-9\/\-]+/',
                    'twitter' => '/twitter\.com\/[a-zA-Z0-9_]+/'
                ];

                foreach ($patterns as $platform => $pattern) {
                    if (preg_match($pattern, $content, $matches)) {
                        $socialMedia[$platform] = 'https://' . $matches[0];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Erro ao buscar redes sociais no website: ' . $e->getMessage());
            }
        }

        // Usa o serviço Serper para buscar redes sociais
        if (empty($socialMedia)) {
            $socialMedia = $this->serper->getSocialMediaPresence([
                'name' => $competitor['name'] ?? parse_url($competitor['website'], PHP_URL_HOST) ?? '',
                'website' => $competitor['website'] ?? ''
            ]);
        }

        return $socialMedia;
    } catch (\Exception $e) {
        Log::error('Erro ao obter presença em redes sociais: ' . $e->getMessage());
        return [];
    }
}

private function analyzeReputation($competitor)
{
    return [
        'rating_analysis' => $this->processReviews($competitor['reviews'] ?? []),
        'sentiment_analysis' => $this->analyzeSentiment($competitor['reviews'] ?? []),
        'review_trends' => $this->analyzeReviewTrends($competitor['reviews'] ?? [])
    ];
}

private function analyzeContent($competitor)
{
    return [
        'value_proposition' => $this->extractValueProposition($competitor),
        'content_quality' => $this->assessContentQuality($competitor),
        'publication_frequency' => $this->analyzePublicationFrequency($competitor),
        'content_topics' => $this->extractContentTopics($competitor)
    ];
}

private function analyzeDemographics($competitor)
{
    return [
        'primary_audience' => $this->identifyPrimaryAudience($competitor),
        'interests' => $this->extractAudienceInterests($competitor),
        'behavior_patterns' => $this->analyzeBehaviorPatterns($competitor)
    ];
}

private function processReviews($reviews)
{
    if (empty($reviews)) {
        return [];
    }

    return array_map(function($review) {
        return [
            'rating' => $review['rating'] ?? 0,
            'text' => $review['text'] ?? '',
            'date' => $review['date'] ?? '',
            'author' => $review['author'] ?? '',
            'language' => $review['language'] ?? 'pt-BR'
        ];
    }, array_slice($reviews, 0, 10)); // Limita a 10 avaliações mais recentes
}

private function analyzeReviews($reviews)
{
    $sentiment = ['positive' => 0, 'negative' => 0, 'neutral' => 0];
    $praises = [];
    $complaints = [];
    $highlighted_reviews = [];

    foreach ($reviews as $review) {
        // Análise de sentimento baseada na avaliação
        if ($review['rating'] >= 4) {
            $sentiment['positive']++;
            if (!empty($review['text'])) {
                $praises[] = $review['text'];
            }
        } elseif ($review['rating'] <= 2) {
            $sentiment['negative']++;
            if (!empty($review['text'])) {
                $complaints[] = $review['text'];
            }
        } else {
            $sentiment['neutral']++;
        }

        // Seleciona avaliações relevantes (com texto substancial)
        if (!empty($review['text']) && strlen($review['text']) > 50) {
            $highlighted_reviews[] = [
                'text' => $review['text'],
                'rating' => $review['rating'],
                'date' => $review['date'],
                'author' => $review['author']
            ];
        }
    }

    return [
        'sentiment' => $this->determineSentiment($sentiment),
        'top_praises' => array_slice($praises, 0, 3),
        'top_complaints' => array_slice($complaints, 0, 3),
        'highlighted_reviews' => array_slice($highlighted_reviews, 0, 3),
        'stats' => $sentiment
    ];
}

private function determineSentiment($sentiment)
{
    $total = array_sum($sentiment);
    if ($total == 0) {
        return 'Não disponível';
    }

    $positivePercentage = ($sentiment['positive'] / $total) * 100;
    $negativePercentage = ($sentiment['negative'] / $total) * 100;

    if ($positivePercentage >= 70) return 'Muito Positivo';
    if ($positivePercentage >= 50) return 'Positivo';
    if ($negativePercentage >= 70) return 'Muito Negativo';
    if ($negativePercentage >= 50) return 'Negativo';
    return 'Neutro';
}

private function calculateCustomerSatisfactionScore($competitor)
{
    // Ensure we have valid rating and review values
    $rating = floatval($competitor['rating'] ?? 0);
    $totalReviews = intval($competitor['total_reviews'] ?? 0);

    // Calculate base score from rating (0-100)
    $baseScore = ($rating / 5) * 100;

    // Weight based on number of reviews
    $reviewWeight = min(1, $totalReviews / 100); // Max weight at 100 reviews

    // Final weighted score
    return round($baseScore * $reviewWeight);
}

private function calculateOnlinePresenceScore($competitor)
{
    $score = 0;
    
    // Website presence
    if (!empty($competitor['website'])) {
        $score += 30;
    }

    // Social media presence
    if (!empty($competitor['social_media'])) {
        $score += count($competitor['social_media']) * 10; // 10 points per social media platform
    }

    // Reviews presence
    if (($competitor['total_reviews'] ?? 0) > 0) {
        $score += min(30, ($competitor['total_reviews'] / 10) * 3); // Max 30 points for reviews
    }

    return min(100, $score); // Cap at 100
}

private function processGeminiResponse($analysis)
{
    $content = is_array($analysis) ? ($analysis['content'] ?? '') : $analysis;
    
    // Inicializar array de retorno
    $processed = [
        'overview' => '',
        'strengths' => [],
        'opportunities' => [],
        'recommendations' => []
    ];

    // Dividir o conteúdo em seções
    $sections = explode("\n", $content);
    $currentSection = null;

    foreach ($sections as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Identificar seções
        if (strpos($line, "Visão geral") !== false || strpos($line, "1.") !== false) {
            $currentSection = 'overview';
            continue;
        } elseif (strpos($line, "Pontos fortes") !== false || strpos($line, "2.") !== false) {
            $currentSection = 'strengths';
            continue;
        } elseif (strpos($line, "Oportunidades") !== false || strpos($line, "3.") !== false) {
            $currentSection = 'opportunities';
            continue;
        } elseif (strpos($line, "Recomendações") !== false || strpos($line, "4.") !== false) {
            $currentSection = 'recommendations';
            continue;
        }

        // Adicionar conteúdo à seção apropriada
        if ($currentSection === 'overview') {
            $processed['overview'] .= $line . "\n";
        } elseif (in_array($currentSection, ['strengths', 'opportunities', 'recommendations'])) {
            if (strpos($line, "- ") === 0 || strpos($line, "• ") === 0) {
                $processed[$currentSection][] = trim(substr($line, 2));
            } else {
                $processed[$currentSection][] = $line;
            }
        }
    }

    return $processed;
}

private function calculateEngagementRate($competitor)
{
    $totalReviews = intval($competitor['total_reviews'] ?? 0);
    $rating = floatval($competitor['rating'] ?? 0);

    if ($totalReviews === 0) {
        return 0;
    }

    // Calculate engagement rate based on reviews and rating
    $engagementRate = ($totalReviews * ($rating / 5)) / 100;
    
    return round($engagementRate * 100, 2); // Convert to percentage with 2 decimal places
}

private function analyzeStrengths($competitorData)
{
    $strengths = [];

    if (isset($competitorData['rating']) && $competitorData['rating'] >= 4.0) {
        $strengths[] = "Excelente reputação com clientes (Rating {$competitorData['rating']}/5)";
    }

    if (isset($competitorData['total_ratings']) && $competitorData['total_ratings'] > 100) {
        $strengths[] = "Base sólida de avaliações ({$competitorData['total_ratings']} reviews)";
    }

    if (!empty($competitorData['photos'])) {
        $strengths[] = "Forte presença visual com " . count($competitorData['photos']) . " fotos";
    }

    if (!empty($competitorData['website'])) {
        $strengths[] = "Presença digital estabelecida com website próprio";
    }

    return $strengths;
}

private function analyzeOpportunities($competitorData)
{
    $opportunities = [];

    if (empty($competitorData['website'])) {
        $opportunities[] = "Concorrente sem presença web - oportunidade para diferenciação digital";
    }

    if (empty($competitorData['photos']) || count($competitorData['photos']) < 5) {
        $opportunities[] = "Oportunidade para melhor apresentação visual do negócio";
    }

    if (!isset($competitorData['rating']) || $competitorData['rating'] < 4.5) {
        $opportunities[] = "Potencial para melhorar a avaliação média dos clientes";
    }

    return $opportunities;
}

private function generateRecommendations($competitorData)
{
    $recommendations = [];

    // Recomendações baseadas na avaliação
    if (isset($competitorData['rating'])) {
        if ($competitorData['rating'] >= 4.5) {
            $recommendations[] = "Focar em manter o alto padrão de qualidade e buscar diferenciais adicionais";
        } else {
            $recommendations[] = "Implementar programa de melhoria contínua para aumentar a satisfação dos clientes";
        }
    }

    // Recomendações baseadas na presença digital
    if (empty($competitorData['website'])) {
        $recommendations[] = "Investir em presença digital forte para se destacar da concorrência";
    }

    // Recomendações baseadas no conteúdo visual
    if (empty($competitorData['photos']) || count($competitorData['photos']) < 5) {
        $recommendations[] = "Desenvolver um portfólio visual mais robusto que o concorrente";
    }

    return $recommendations;
}

private function getRatingAnalysis($rating)
{
    if ($rating >= 4.5) {
        return "uma performance excepcional no mercado.";
    } elseif ($rating >= 4.0) {
        return "uma boa performance, com espaço para melhorias.";
    } elseif ($rating >= 3.5) {
        return "uma performance mediana, indicando oportunidades significativas.";
    } else {
        return "desafios significativos na satisfação do cliente.";
    }
}

private function analyzeCompetitorData($competitor)
{
    // Usar cache para otimizar performance
    $cacheKey = 'competitor_analysis_' . md5(json_encode($competitor));
    
    return Cache::remember($cacheKey, now()->addHours(24), function () use ($competitor) {
        $keywords = $this->serper->extractKeywords($competitor['name']);
        $socialPresence = $this->serper->getSocialMediaPresence($competitor);
        $searchVolume = $this->serper->getSearchVolume($competitor['name']);
        
        return [
            'keywords' => $keywords,
            'social_presence' => $socialPresence,
            'search_volume' => $searchVolume,
            'metrics' => [
                'keyword_strength' => $this->calculateKeywordStrength($keywords),
                'social_engagement' => $this->calculateSocialEngagement($socialPresence),
                'market_presence' => $this->calculateMarketPresence($searchVolume)
            ]
        ];
    });
}

private function calculateKeywordStrength($keywords)
{
    $totalMentions = array_sum($keywords);
    $uniqueKeywords = count($keywords);
    
    return min(10, ($totalMentions / 100) + ($uniqueKeywords / 10));
}

private function calculateSocialEngagement($socialPresence)
{
    $score = 0;
    foreach ($socialPresence as $platform => $data) {
        if ($data !== null) {
            $score += 2.5; // 2.5 pontos por plataforma ativa
        }
    }
    return min(10, $score);
}

private function calculateMarketPresence($searchVolume)
{
    return min(10, ($searchVolume['monthly_searches'] / 1000));
}


private function getCachedAnalysis($competitor)
{
    $cacheKey = 'competitor_analysis_' . md5(json_encode($competitor));
    
    return Cache::remember($cacheKey, now()->addHours(24), function () use ($competitor) {
        return $this->performFullAnalysis($competitor);
    });
}

private function performFullAnalysis($competitor)
{
    $basicInfo = $this->serper->searchSpecificCompetitor($competitor['name'], $competitor['address']);
    $keywords = $this->serper->extractKeywords($competitor['name']);
    $socialPresence = $this->serper->getSocialMediaPresence($competitor);
    $searchVolume = $this->serper->getSearchVolume($competitor['name']);

    return [
        'basic_info' => $basicInfo,
        'keywords' => $keywords,
        'social_presence' => $socialPresence,
        'search_volume' => $searchVolume,
        'metrics' => [
            'keyword_strength' => $this->calculateKeywordStrength($keywords),
            'social_engagement' => $this->calculateSocialEngagement($socialPresence),
            'market_presence' => $this->calculateMarketPresence($searchVolume)
        ],
        'updated_at' => now()
    ];
}

public function invalidateCache($competitor)
{
    $cacheKey = 'competitor_analysis_' . md5(json_encode($competitor));
    Cache::forget($cacheKey);
}
    
}