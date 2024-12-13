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
}