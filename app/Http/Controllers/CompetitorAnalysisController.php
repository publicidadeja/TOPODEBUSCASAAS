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
    
    private function generateRecommendations($competitors)
{
    try {
        $prompt = "Analise os seguintes dados de concorrentes e forneça exatamente 3 recomendações estratégicas.\n\n";
        $prompt .= "Dados dos concorrentes:\n";
        foreach ($competitors as $data) { // Changed from $competitorsData to $competitors
            $prompt .= "- Empresa: {$data['name']}\n";
            $prompt .= "  Avaliação: {$data['rating']}/5\n";
            $prompt .= "  Número de reviews: {$data['reviews']}\n";
            $prompt .= "  Possui website: " . (!empty($data['website']) ? 'Sim' : 'Não') . "\n";
            $prompt .= "  Localização: {$data['location']}\n\n";
        }
        
        $prompt .= "\nForneça suas recomendações no seguinte formato JSON:\n";
        $prompt .= "{\n";
        $prompt .= "  \"recommendations\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"title\": \"título da recomendação\",\n";
        $prompt .= "      \"description\": \"descrição detalhada\",\n";
        $prompt .= "      \"priority\": \"high/medium/low\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ]\n";
        $prompt .= "}";

        // Obter resposta do Gemini
        $geminiAnalysis = $this->gemini->generateResponse($prompt);
        
        // Tentar decodificar o JSON
        $decodedResponse = json_decode($geminiAnalysis, true);
        
        // Validar a resposta
        if (json_last_error() === JSON_ERROR_NONE && 
            isset($decodedResponse['recommendations']) && 
            !empty($decodedResponse['recommendations'])) {
            
            return $decodedResponse['recommendations'];
        }

        // Log do erro se a resposta não for válida
        \Log::warning('Resposta do Gemini não está no formato esperado', [
            'response' => $geminiAnalysis
        ]);
        
        return $this->generateDefaultRecommendations($competitors);

    } catch (\Exception $e) {
        \Log::error('Erro ao gerar recomendações com Gemini: ' . $e->getMessage());
        return $this->generateDefaultRecommendations($competitors);
    }
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

public function analyzeSingle(Request $request)
{
    try {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
            'competitor_data' => 'required|array'
        ]);

        // Análise básica do concorrente
        $analysis = [
            'overview' => $this->generateOverview($validated['competitor_data']),
            'strengths' => $this->analyzeStrengths($validated['competitor_data']),
            'opportunities' => $this->analyzeOpportunities($validated['competitor_data']),
            'recommendations' => $this->generateRecommendations($validated['competitor_data'])
        ];

        return response()->json([
            'success' => true,
            'analysis' => $analysis
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao analisar concorrente: ' . $e->getMessage()
        ], 500);
    }
}

private function generateOverview($competitorData)
{
    $rating = $competitorData['rating'] ?? 'N/A';
    $reviews = $competitorData['total_ratings'] ?? 0;
    
    return "Este estabelecimento possui uma avaliação média de {$rating} estrelas baseada em {$reviews} avaliações. " .
           "Localizado em {$competitorData['address']}, o negócio demonstra " .
           $this->getRatingAnalysis($rating);
}

private function analyzeStrengths($competitorData)
{
    $strengths = [];
    
    if (isset($competitorData['rating']) && $competitorData['rating'] >= 4.5) {
        $strengths[] = "Excelente reputação com clientes (Rating {$competitorData['rating']}/5)";
    }
    
    if (isset($competitorData['total_ratings']) && $competitorData['total_ratings'] > 100) {
        $strengths[] = "Base sólida de avaliações ({$competitorData['total_ratings']} reviews)";
    }
    
    if (!empty($competitorData['photos'])) {
        $strengths[] = "Forte presença visual com " . count($competitorData['photos']) . " fotos";
    }
    
    return $strengths;
}

private function analyzeOpportunities($competitorData)
{
    $opportunities = [];
    
    if (isset($competitorData['rating']) && $competitorData['rating'] < 4.5) {
        $opportunities[] = "Potencial para superar a avaliação média do concorrente";
    }
    
    if (empty($competitorData['website'])) {
        $opportunities[] = "Concorrente sem presença web - oportunidade para diferenciação digital";
    }
    
    if (empty($competitorData['photos']) || count($competitorData['photos']) < 5) {
        $opportunities[] = "Oportunidade para melhor apresentação visual do negócio";
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
            $recommendations[] = "Identificar pontos de melhoria para superar a avaliação do concorrente";
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