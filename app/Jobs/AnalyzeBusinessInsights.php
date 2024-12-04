<?php
namespace App\Jobs;

use App\Models\Business;
use App\Services\AIAnalysisService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeBusinessInsights implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $business;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    public function handle(AIAnalysisService $aiService, NotificationService $notificationService)
    {
        try {
            // Realizar análise
            $insights = $aiService->analyzeBusinessData($this->business);

            // Processar insights importantes
            foreach ($insights as $insight) {
                if ($insight['priority'] === 'high') {
                    $notificationService->createInsightNotification(
                        $this->business,
                        $insight['title'],
                        $insight['message'],
                        $insight['action_type'] ?? null,
                        $insight['action_data'] ?? null
                    );
                }
            }

            // Atualizar métricas de automação
            $this->business->automationMetrics()->create([
                'analysis_date' => now(),
                'insights_count' => count($insights),
                'high_priority_count' => collect($insights)->where('priority', 'high')->count(),
                'data' => json_encode($insights)
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro na análise de insights: ' . $e->getMessage());
            throw $e;
        }
    }
}