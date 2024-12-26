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
        $name = $request->input('name');
        $address = $request->input('address');
        $competitorData = $request->input('competitor_data');

        // Verificar se competitorData é uma string JSON e converter para array
        if (is_string($competitorData)) {
            $competitorData = json_decode($competitorData, true);
        }

        // Definir valores padrão para os dados do competidor
        $competitorData = array_merge([
            'name' => $name,
            'address' => $address,
            'rating' => 0,
            'reviews' => 0,
            'website' => '',
            'status' => 'OPERATIONAL'
        ], $competitorData ?? []);

        // Construir a prompt para a análise
        $prompt = "Analise o seguinte estabelecimento comercial:\n" .
                 "Nome: {$competitorData['name']}\n" .
                 "Endereço: {$competitorData['address']}\n" .
                 "Avaliação: {$competitorData['rating']}\n" .
                 "Total de Avaliações: {$competitorData['reviews']}\n" .
                 "Forneça uma análise detalhada incluindo:\n" .
                 "1. Visão geral do negócio\n" .
                 "2. Pontos fortes\n" .
                 "3. Oportunidades de melhoria\n" .
                 "4. Recomendações estratégicas";

        // Usar o GeminiService para gerar a análise
        $analysis = $this->gemini->generateContent($prompt);

        // Processar a resposta do Gemini
        $processedAnalysis = $this->processGeminiResponse($analysis);

        // Formatar a resposta
        $formattedAnalysis = [
            'overview' => $processedAnalysis['overview'] ?? 'Análise não disponível',
            'strengths' => $processedAnalysis['strengths'] ?? [],
            'opportunities' => $processedAnalysis['opportunities'] ?? [],
            'recommendations' => $processedAnalysis['recommendations'] ?? [],
            'metrics' => [
                'rating' => $competitorData['rating'] ?? 0,
                'reviews' => $competitorData['reviews'] ?? 0,
                'engagement_rate' => $this->calculateEngagementRate($competitorData)
            ]
        ];

        return response()->json([
            'success' => true,
            'analysis' => $formattedAnalysis
        ]);
    } catch (\Exception $e) {
        Log::error('Erro na análise do concorrente: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao analisar concorrente: ' . $e->getMessage()
        ], 500);
    }
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

private function calculateEngagementRate($competitorData)
{
    $reviews = $competitorData['reviews'] ?? 0;
    $rating = $competitorData['rating'] ?? 0;
    
    if ($reviews > 0 && $rating > 0) {
        return round(($reviews * $rating) / 100, 2);
    }
    
    return 0;
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
    
}