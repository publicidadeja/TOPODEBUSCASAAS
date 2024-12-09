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
        $request->validate([
            'business_id' => 'required|exists:businesses,id'
        ]);

        $business = Business::findOrFail($request->business_id);
        
        // Busca concorrentes usando o Serper
        $competitors = $this->searchCompetitors($business);
        
        if (empty($competitors)) {
            throw new \Exception('Não foi possível encontrar concorrentes');
        }
        
        // Analisa os dados com o Gemini
        $analysis = $this->aiAnalysis->analyzeCompetitors($business, $competitors);
        
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
        \Log::error('Erro na análise de concorrentes: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao atualizar análise: ' . $e->getMessage()
        ], 500);
    }
}

private function searchCompetitors($business)
{
    if (empty($business->segment) || empty($business->city) || empty($business->state)) {
        throw new \Exception('Dados do negócio incompletos para busca de concorrentes');
    }

    $query = sprintf(
        '%s em %s %s',
        trim($business->segment),
        trim($business->city),
        trim($business->state)
    );

    $results = $this->serper->search($query);

    if (empty($results)) {
        throw new \Exception('Nenhum resultado encontrado para a busca');
    }

    return $results;
}

    
}