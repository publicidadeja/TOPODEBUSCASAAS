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
        $competitors = $business->competitors()
            ->with(['analytics', 'reviews'])
            ->get();

        $analysis = $this->geminiService->analyzeMarketData($business, $competitors);
        
        if (!$analysis['success']) {
            return response()->json([
                'error' => $analysis['error']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $analysis['data']
        ]);

    } catch (\Exception $e) {
        Log::error('Erro na análise de mercado: ' . $e->getMessage());
        return response()->json([
            'error' => 'Erro ao processar análise'
        ], 500);
    }
}

public function exportAnalysisPDF(Business $business)
{
    try {
        // Obter dados dos concorrentes
        $competitors = $business->competitors()->with(['analytics', 'reviews'])->get();
        
        // Obter análise do Gemini
        $analysis = $this->geminiService->analyzeMarketData($business, $competitors);
        
        if (!$analysis['success']) {
            return response()->json(['error' => $analysis['error']], 422);
        }

        // Preparar dados para o PDF
        $data = [
            'business' => $business,
            'analysis' => $analysis['data'],
            'period' => [
                'start' => now()->subDays(30)->format('d/m/Y'),
                'end' => now()->format('d/m/Y')
            ],
            'competitors' => $competitors
        ];

        // Gerar PDF usando a view existente
        $pdf = PDF::loadView('analytics.exports.pdf', $data);
        
        return $pdf->download('analise-detalhada-' . $business->name . '.pdf');

    } catch (\Exception $e) {
        Log::error('Erro ao gerar PDF de análise: ' . $e->getMessage());
        return response()->json(['error' => 'Erro ao gerar relatório'], 500);
    }
}
}