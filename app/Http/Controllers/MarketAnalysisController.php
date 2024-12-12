<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketAnalysisController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function analyze(Request $request, Business $business)
    {
        try {
            // Busca os concorrentes
            $competitors = $business->competitors()
                ->with(['analytics', 'reviews'])
                ->get();

            // Realiza a análise
            $analysis = $this->geminiService->analyzeMarketData($business, $competitors);

            return response()->json($analysis);

        } catch (\Exception $e) {
            Log::error('Erro na análise de mercado: ' . $e->getMessage());
            return response()->json([
                'market_overview' => 'Erro ao gerar análise de mercado',
                'competitor_analysis' => 'Erro ao analisar concorrentes',
                'opportunities' => 'Erro ao identificar oportunidades',
                'recommendations' => 'Erro ao gerar recomendações'
            ], 500);
        }
    }
}