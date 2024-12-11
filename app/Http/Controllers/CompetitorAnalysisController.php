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
            'city' => $business->city,
            'state' => $business->state
        ]
    ]);

    if (empty($business->segment) || empty($business->city) || empty($business->state)) {
        \Log::warning('Dados do negócio incompletos', [
            'segment' => $business->segment,
            'city' => $business->city,
            'state' => $business->state
        ]);
        throw new \Exception('Dados do negócio incompletos para busca de concorrentes');
    }

    $query = sprintf(
        '%s em %s %s',
        trim($business->segment),
        trim($business->city),
        trim($business->state)
    );

    \Log::info('Executando busca', ['query' => $query]);
    
    try {
        $results = $this->serper->search($query);
        \Log::info('Resultados obtidos', [
            'count' => count($results)
        ]);
        return $results;
    } catch (\Exception $e) {
        \Log::error('Erro na busca Serper', [
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

    
}