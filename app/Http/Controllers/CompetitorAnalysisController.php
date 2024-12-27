<?php


namespace App\Http\Controllers;

use App\Services\AIAnalysisService;
use App\Services\SerperService;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Services\GeminiService;

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
        // Validação dos dados de entrada
        $name = $request->input('name');
        $address = $request->input('address');
        
        if (empty($name) || empty($address)) {
            throw new \Exception('Nome e endereço do concorrente são obrigatórios');
        }

        // Busca dados do concorrente usando SerperService
        $searchResults = $this->serper->searchSpecificCompetitor($name, $address);
        
        if (empty($searchResults)) {
            throw new \Exception('Não foi possível encontrar dados do concorrente');
        }

        // Formata os dados para análise
        $competitorData = [
            'name' => $name,
            'address' => $address,
            'rating' => floatval($searchResults['rating'] ?? 0),
            'reviews_count' => intval($searchResults['reviews_count'] ?? 0),
            'website' => strval($searchResults['website'] ?? ''),
            'phone' => strval($searchResults['phone'] ?? ''),
            'business_status' => strval($searchResults['business_status'] ?? ''),
            'price_level' => strval($searchResults['price_level'] ?? ''),
            'categories' => is_array($searchResults['categories']) ? 
                          implode(', ', $searchResults['categories']) : 
                          strval($searchResults['categories'] ?? ''),
            'hours' => is_array($searchResults['hours']['weekday_text'] ?? []) ? 
                      implode("\n", $searchResults['hours']['weekday_text']) : ''
        ];

        // Constrói o prompt para o Gemini
        $prompt = "Por favor, analise o seguinte estabelecimento comercial:\n\n" .
                 "Nome: {$competitorData['name']}\n" .
                 "Endereço: {$competitorData['address']}\n" .
                 "Avaliação: {$competitorData['rating']}\n" .
                 "Total de Avaliações: {$competitorData['reviews_count']}\n" .
                 "Website: {$competitorData['website']}\n" .
                 "Telefone: {$competitorData['phone']}\n" .
                 "Status: {$competitorData['business_status']}\n" .
                 "Nível de Preço: {$competitorData['price_level']}\n" .
                 "Categorias: {$competitorData['categories']}\n" .
                 "Horários:\n{$competitorData['hours']}\n\n" .
                 "Por favor, forneça:\n" .
                 "1. Uma visão geral do negócio\n" .
                 "2. Pontos fortes identificados\n" .
                 "3. Oportunidades de melhoria\n" .
                 "4. Recomendações estratégicas\n" .
                 "5. Análise da presença online\n" .
                 "6. Avaliação da competitividade no mercado local";

        // Obtém análise do Gemini
        $analysis = $this->gemini->generateContent($prompt);

        // Retorna a resposta formatada
        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'data' => [
                'business' => $competitorData,
                'raw_data' => $searchResults
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro na análise do concorrente: ' . $e->getMessage(), [
            'name' => $name ?? null,
            'address' => $address ?? null,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao analisar concorrente: ' . $e->getMessage()
        ], 500);
    }
}

private function processGeminiResponse($analysis)
{
    $content = is_array($analysis) ? ($analysis['content'] ?? '') : $analysis;
    
    // Initialize return array
    $processed = [
        'overview' => '',
        'strengths' => [],
        'opportunities' => [],
        'recommendations' => []
    ];

    // Split content into sections
    $sections = explode("\n", $content);
    $currentSection = null;

    foreach ($sections as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Identify sections
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

        // Add content to appropriate section
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

// Adicione este método se não existir
private function generateOverview($competitorData, $metrics)
{
    $ratingAnalysis = $this->getRatingAnalysis($metrics['rating']);
    
    return sprintf(
        "O estabelecimento %s, localizado em %s, apresenta %s " .
        "Com uma média de %.1f estrelas baseada em %d avaliações, " .
        "demonstra uma taxa de engajamento de %.1f%%.",
        $competitorData['name'],
        $competitorData['address'],
        $ratingAnalysis,
        $metrics['rating'],
        $metrics['total_ratings'],
        $metrics['engagement_rate']
    );
}

private function analyzeReviews($reviews)
{
    if (empty($reviews)) {
        return null;
    }

    // Análise de sentimento e temas comuns
    $sentiments = [
        'positive' => 0,
        'negative' => 0,
        'neutral' => 0
    ];

    $commonThemes = [];

    foreach ($reviews as $review) {
        // Análise básica de sentimento baseada na avaliação
        $rating = $review['rating'] ?? 0;
        if ($rating >= 4) {
            $sentiments['positive']++;
        } elseif ($rating <= 2) {
            $sentiments['negative']++;
        } else {
            $sentiments['neutral']++;
        }

        // Extrair texto do review para análise
        $text = $review['text'] ?? '';
        if (!empty($text)) {
            // Aqui você pode implementar uma análise mais detalhada do texto
            // Por exemplo, identificar palavras-chave comuns
        }
    }

    return [
        'sentiment_analysis' => $sentiments,
        'common_themes' => $commonThemes,
        'total_analyzed' => count($reviews)
    ];
}

private function prepareReviewsText($reviews)
{
    $texts = array_map(function ($review) {
        return $review['text'] ?? '';
    }, $reviews);

    return implode("\n", array_filter($texts));
}


private function analyzeReviewsSentiment($reviewsText)
{
    // Aqui você pode implementar uma análise de sentimento dos reviews
    // Por exemplo, usando o serviço Gemini ou outro serviço de análise
    return $reviewsText ? "Análise de sentimento dos comentários em desenvolvimento." : "";
}



private function calculateEngagementRate($data)
{
    $totalRatings = $data['total_ratings'] ?? 0;
    $rating = $data['rating'] ?? 0;
    
    return $totalRatings > 0 ? round(($rating * $totalRatings) / 100, 2) : 0;
}

private function analyzeStrengths($data)
{
    $strengths = [];
    $rating = $data['rating'] ?? 0;
    $totalRatings = $data['total_ratings'] ?? 0;

    if ($rating >= 4.5) {
        $strengths[] = "Excelente avaliação dos clientes ({$rating}/5)";
    }

    if ($totalRatings > 100) {
        $strengths[] = "Grande volume de avaliações ({$totalRatings} avaliações)";
    }

    if (!empty($data['photos'])) {
        $strengths[] = "Boa presença visual com " . count($data['photos']) . " fotos do estabelecimento";
    }

    if (!empty($data['website'])) {
        $strengths[] = "Presença digital estabelecida com website próprio";
    }

    return $strengths;
}

private function analyzeOpportunities($data)
{
    $opportunities = [];
    $rating = $data['rating'] ?? 0;
    $totalRatings = $data['total_ratings'] ?? 0;

    if ($rating < 4.5) {
        $opportunities[] = "Potencial para melhorar a avaliação geral (atual: {$rating}/5)";
    }

    if ($totalRatings < 100) {
        $opportunities[] = "Oportunidade para aumentar o número de avaliações (atual: {$totalRatings})";
    }

    if (empty($data['website'])) {
        $opportunities[] = "Criar presença online com website próprio";
    }

    if (empty($data['photos'])) {
        $opportunities[] = "Adicionar fotos do estabelecimento para melhor visibilidade";
    }

    return $opportunities;
}

private function generateRecommendations($data)
{
    $recommendations = [];
    $rating = $data['rating'] ?? 0;

    if ($rating < 4.0) {
        $recommendations[] = "Implementar programa de melhoria de qualidade para aumentar avaliações";
    }

    if (empty($data['website'])) {
        $recommendations[] = "Desenvolver website próprio para fortalecer presença digital";
    }

    if (count($data['photos'] ?? []) < 5) {
        $recommendations[] = "Adicionar mais fotos de qualidade do estabelecimento e serviços";
    }

    if (count($data['reviews'] ?? []) < 50) {
        $recommendations[] = "Criar estratégia para incentivar mais avaliações dos clientes";
    }

    return $recommendations;
}



private function getRatingAnalysis($rating)
{
    if ($rating >= 4.5) {
        return "A avaliação é excelente, indicando alta satisfação dos clientes.";
    } elseif ($rating >= 4.0) {
        return "A avaliação é muito boa, com boa aceitação dos clientes.";
    } elseif ($rating >= 3.5) {
        return "A avaliação é regular, com espaço para melhorias.";
    } else {
        return "A avaliação está abaixo da média, necessitando atenção especial.";
    }
}
    
}