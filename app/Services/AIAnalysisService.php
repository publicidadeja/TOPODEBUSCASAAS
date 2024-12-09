<?php

namespace App\Services;

class AIAnalysisService
{
    protected $gemini;
    protected $serper;

    public function __construct(GeminiService $gemini, SerperService $serper)
    {
        $this->gemini = $gemini;
        $this->serper = $serper;
    }

    public function analyzeBusinessPerformance($business)
    {
        // Coleta métricas dos últimos 30 dias
        $endDate = now();
        $startDate = now()->subDays(30);
        
        $currentPeriod = $business->analytics()
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
            
        $previousPeriod = $business->analytics()
            ->whereBetween('date', [$startDate->copy()->subDays(30), $startDate])
            ->get();

        // Calcula métricas atuais
        $metrics = [
            'views' => $currentPeriod->sum('views'),
            'clicks' => $currentPeriod->sum('clicks'),
            'calls' => $currentPeriod->sum('calls'),
            'reviews' => $business->reviews()->count(),
            'average_rating' => $business->reviews()->avg('rating'),
            'conversion_rate' => $currentPeriod->sum('clicks') > 0 
                ? round(($currentPeriod->sum('calls') / $currentPeriod->sum('clicks')) * 100, 1)
                : 0,
        ];

        // Calcula variações em relação ao período anterior
        $variations = [
            'views' => $this->calculateVariation(
                $previousPeriod->sum('views'),
                $currentPeriod->sum('views')
            ),
            'clicks' => $this->calculateVariation(
                $previousPeriod->sum('clicks'),
                $currentPeriod->sum('clicks')
            ),
            'calls' => $this->calculateVariation(
                $previousPeriod->sum('calls'),
                $currentPeriod->sum('calls')
            ),
            'conversion_rate' => $this->calculateVariation(
                $previousPeriod->sum('clicks') > 0 
                    ? ($previousPeriod->sum('calls') / $previousPeriod->sum('clicks')) * 100 
                    : 0,
                $metrics['conversion_rate']
            )
        ];

        // Gera o prompt para o Gemini
        $prompt = $this->generateAnalysisPrompt($business, $metrics, $variations);

        // Obtém análise do Gemini
        $analysis = $this->gemini->analyze($prompt);

        // Estrutura o retorno com insights reais
        return [
            'performance' => [
                'type' => 'performance',
                'message' => $this->generatePerformanceInsight($metrics, $variations)
            ],
            'opportunities' => [
                'type' => 'opportunity',
                'message' => $this->generateOpportunityInsight($metrics, $variations)
            ],
            'alerts' => [
                'type' => 'alert',
                'message' => $this->generateAlertInsight($metrics, $variations)
            ]
        ];
    }

    private function calculateVariation($previous, $current)
    {
        if ($previous == 0) return 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function generatePerformanceInsight($metrics, $variations)
    {
        $messages = [];

        if ($variations['views'] > 0) {
            $messages[] = "Aumento de {$variations['views']}% nas visualizações";
        }

        if ($variations['conversion_rate'] > 0) {
            $messages[] = "Melhoria na taxa de conversão de {$variations['conversion_rate']}%";
        }

        if ($metrics['average_rating'] >= 4.5) {
            $messages[] = "Excelente avaliação média de {$metrics['average_rating']} estrelas";
        }

        return !empty($messages) 
            ? implode(". ", $messages) . "."
            : "Mantenha o monitoramento para identificar tendências de performance.";
    }

    private function generateOpportunityInsight($metrics, $variations)
    {
        $opportunities = [];

        if ($metrics['conversion_rate'] < 2) {
            $opportunities[] = "Potencial para melhorar taxa de conversão que está em {$metrics['conversion_rate']}%";
        }

        if ($variations['clicks'] < 0) {
            $opportunities[] = "Oportunidade de otimizar CTAs para recuperar queda de {$variations['clicks']}% nos cliques";
        }

        if ($metrics['average_rating'] < 4.0) {
            $opportunities[] = "Trabalhe na satisfação do cliente para melhorar a nota média de {$metrics['average_rating']}";
        }

        return !empty($opportunities)
            ? implode(". ", $opportunities) . "."
            : "Continue monitorando para identificar novas oportunidades de crescimento.";
    }

    private function generateAlertInsight($metrics, $variations)
    {
        $alerts = [];

        if ($variations['views'] < -10) {
            $alerts[] = "Queda significativa de {$variations['views']}% nas visualizações";
        }

        if ($variations['calls'] < -15) {
            $alerts[] = "Redução preocupante de {$variations['calls']}% nas ligações";
        }

        if ($metrics['average_rating'] < 3.5) {
            $alerts[] = "Avaliação média baixa de {$metrics['average_rating']} estrelas requer atenção imediata";
        }

        return !empty($alerts)
            ? implode(". ", $alerts) . "."
            : "Nenhum alerta crítico identificado no momento.";
    }

    private function generateAnalysisPrompt($business, $metrics, $variations)
    {
        return "Analise os seguintes dados do negócio {$business->name}:
                Métricas atuais:
                - Visualizações: {$metrics['views']}
                - Cliques: {$metrics['clicks']}
                - Ligações: {$metrics['calls']}
                - Taxa de conversão: {$metrics['conversion_rate']}%
                - Avaliações: {$metrics['reviews']}
                - Nota média: {$metrics['average_rating']}

                Variações em relação ao período anterior:
                - Visualizações: {$variations['views']}%
                - Cliques: {$variations['clicks']}%
                - Ligações: {$variations['calls']}%
                - Taxa de conversão: {$variations['conversion_rate']}%

                Forneça uma análise detalhada incluindo:
                1. Performance geral
                2. Oportunidades de melhoria
                3. Alertas importantes";
    }

    public function analyzeCompetitors($business, $competitors)
{
    $prompt = "Analise os seguintes concorrentes para o negócio {$business->name} 
               no segmento {$business->segment} em {$business->city}:\n\n";
    
    foreach ($competitors as $competitor) {
        $prompt .= "Concorrente: {$competitor['title']}\n";
        $prompt .= "Descrição: {$competitor['snippet']}\n\n";
    }
    
    $prompt .= "Forneça uma análise detalhada incluindo:\n";
    $prompt .= "1. Principais concorrentes\n";
    $prompt .= "2. Análise de mercado\n";
    $prompt .= "3. Recomendações estratégicas\n";
    
    return $this->gemini->analyze($prompt);
}
}