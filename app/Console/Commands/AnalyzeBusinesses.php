<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Services\GeminiService;
use App\Services\AnalyticsService;

class AnalyzeBusinesses extends Command
{
    protected $signature = 'businesses:analyze';
    protected $description = 'Analisa todos os negócios ativos usando IA';

    public function handle(GeminiService $gemini, AnalyticsService $analytics)
{
    $businesses = Business::active()->get();

    foreach ($businesses as $business) {
        // Coleta dados analíticos
        $analyticsData = $analytics->getDataForBusiness($business->id);
        
        // Gera análise com IA
        $analysis = $gemini->analyzeBusinessData($business, $analyticsData);
        
        // Salva análise
        $business->analyses()->create([
            'content' => $analysis['analysis'],
            'generated_at' => now()
        ]);
    }
}
}