<?php


namespace App\Http\Controllers;

use App\Services\AIAnalysisService;
use App\Services\SerperService;
use App\Models\Business;

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
        \Log::info('Iniciando análise de concorrentes', [
            'business_id' => $request->business_id
        ]);

        $request->validate([
            'business_id' => 'required|exists:businesses,id'
        ]);

        $business = Business::findOrFail($request->business_id);
        \Log::info('Negócio encontrado', [
            'business' => $business->toArray()
        ]);
        
        // Log antes de buscar concorrentes
        \Log::info('Buscando concorrentes');
        $competitors = $this->searchCompetitors($business);
        \Log::info('Concorrentes encontrados', [
            'count' => count($competitors)
        ]);
        
        if (empty($competitors)) {
            throw new \Exception('Não foi possível encontrar concorrentes');
        }
        
        // Log antes da análise
        \Log::info('Iniciando análise com Gemini');
        $analysis = $this->aiAnalysis->analyzeCompetitors($business, $competitors);
        \Log::info('Análise completada', [
            'analysis' => $analysis
        ]);
        
        if (empty($analysis)) {
            throw new \Exception('Erro ao analisar dados dos concorrentes');
        }

        return response()->json([
            'success' => true,
            'competitors' => $analysis['competitors'] ?? [],
            'marketAnalysis' => $analysis['market_analysis'] ?? [],
            'recommendations' => $analysis['recommendations'] ?? []
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro na análise de concorrentes', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao atualizar análise: ' . $e->getMessage()
        ], 500);
    }
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