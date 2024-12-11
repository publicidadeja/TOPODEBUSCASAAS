<?php


namespace App\Http\Controllers;

use App\Services\AIAnalysisService;
use App\Services\SerperService;
use App\Models\Business;
use Illuminate\Http\Request;

class CompetitorAnalysisController extends Controller
{
    protected $aiAnalysis;
    protected $serper;

    public function __construct(AIAnalysisService $aiAnalysis, SerperService $serper)
    {
        $this->aiAnalysis = $aiAnalysis;
        $this->serper = $serper;
    }

    public function analyze(Request $request)
{
    try {
        $business = Business::findOrFail($request->business_id);
        
        // Busca os concorrentes
        $competitors = $this->searchCompetitors($business);
        
        // Formata os dados dos concorrentes
        $formattedCompetitors = array_map(function($competitor) {
            return [
                'name' => $competitor['title'] ?? 'Nome não disponível',
                'location' => $competitor['location'] ?? 'Localização não disponível',
                'score' => rand(1, 10), // Exemplo - implemente sua própria lógica de score
                'summary' => $competitor['snippet'] ?? 'Resumo não disponível'
            ];
        }, $competitors);

        // Retorna os dados formatados
        return response()->json([
            'success' => true,
            'competitors' => $formattedCompetitors,
            'marketAnalysis' => [
                [
                    'title' => 'Análise de Mercado',
                    'description' => 'Descrição da análise de mercado'
                ]
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro na análise de concorrentes: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao atualizar análise: ' . $e->getMessage()
        ], 500);
    }
}
private function generateDefaultAnalysis($competitors)
{
    $competitorCount = count($competitors);
    $averageScore = array_reduce($competitors, function($carry, $competitor) {
        return $carry + ($competitor['score'] ?? 0);
    }, 0) / max(1, $competitorCount);

    return [
        'performance' => [
            'type' => 'performance',
            'message' => sprintf(
                'Análise baseada em %d concorrentes principais. Score médio do mercado: %.1f/10',
                $competitorCount,
                $averageScore
            )
        ],
        'opportunities' => [
            'type' => 'opportunity',
            'message' => 'Identificadas oportunidades de diferenciação no mercado com base nos concorrentes analisados.'
        ],
        'alerts' => [
            'type' => 'alert',
            'message' => 'Monitorando atividades dos principais concorrentes para identificar tendências e mudanças no mercado.'
        ],
        'market_analysis' => [
            [
                'title' => 'Análise de Mercado',
                'description' => sprintf(
                    'O mercado apresenta %d players principais, com diferentes níveis de presença online e reputação.',
                    $competitorCount
                )
            ]
        ]
    ];
}

private function generateRecommendations($competitors)
{
    $recommendations = [];
    foreach ($competitors as $index => $competitor) {
        if ($index < 3) { // Limita a 3 recomendações
            $recommendations[] = [
                'title' => 'Análise de Concorrente: ' . ($competitor['title'] ?? 'Concorrente ' . ($index + 1)),
                'description' => 'Avalie as estratégias e diferenciais deste concorrente.',
                'priority' => 'medium'
            ];
        }
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
    
}