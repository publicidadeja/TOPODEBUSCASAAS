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
            $business = Business::findOrFail($request->business_id);
            
            // Busca concorrentes usando o Serper
            $competitors = $this->searchCompetitors($business);
            
            // Analisa os dados com o Gemini
            $analysis = $this->aiAnalysis->analyzeCompetitors($business, $competitors);
            
            // Salva a análise no banco de dados
            $business->competitor_analyses()->create([
                'data' => $analysis,
                'analyzed_at' => now()
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function searchCompetitors($business)
    {
        $query = "{$business->segment} em {$business->city} {$business->state}";
        return $this->serper->search($query);
    }
}