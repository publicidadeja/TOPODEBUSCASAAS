<?php

namespace App\Jobs;

use App\Models\Business;
use App\Services\AIAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeBusinessData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $business;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    public function handle(AIAnalysisService $aiService)
    {
        try {
            // Realizar análise
            $analysis = $aiService->analyzeBusinessPerformance($this->business);

            // Gerar insights
            $insights = $aiService->generateInsights($analysis);

            // Criar notificações para insights importantes
            foreach ($insights as $insight) {
                if ($insight['priority'] === 'high') {
                    $this->createInsightNotification($insight);
                }
            }

            // Atualizar métricas de automação
            $this->updateAutomationMetrics($analysis);

        } catch (\Exception $e) {
            \Log::error('Erro na análise automática: ' . $e->getMessage());
        }
    }

    private function createInsightNotification($insight)
    {
        $this->business->notifications()->create([
            'type' => 'insight',
            'title' => $insight['title'],
            'message' => $insight['message'],
            'priority' => $insight['priority'],
            'data' => json_encode($insight['data'])
        ]);
    }

    private function updateAutomationMetrics($analysis)
    {
        $this->business->automationMetrics()->create([
            'analysis_date' => now(),
            'metrics' => json_encode($analysis),
            'success_rate' => $analysis['success_rate'] ?? 0,
            'automation_score' => $analysis['automation_score'] ?? 0
        ]);
    }
}