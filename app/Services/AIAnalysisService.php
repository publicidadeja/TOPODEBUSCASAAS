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
    // Adicione mais contexto e estrutura aos dados
    $prompt = "Analise detalhadamente os seguintes concorrentes:\n\n";
    foreach ($competitors as $competitor) {
        $prompt .= "Nome: {$competitor['title']}\n";
        $prompt .= "Avaliação: {$competitor['rating']}\n";
        $prompt .= "Número de Reviews: {$competitor['reviews']}\n";
        $prompt .= "Localização: {$competitor['location']}\n\n";
    }
    
    $prompt .= "\nCom base nesses dados, forneça:\n";
    $prompt .= "1. Análise competitiva detalhada\n";
    $prompt .= "2. Recomendações estratégicas específicas\n";
    $prompt .= "3. Oportunidades de melhoria\n";
    
    return $this->gemini->analyze($prompt);
}

public function analyzeKeywords($business, $keywords)
{
    $prompt = $this->buildKeywordAnalysisPrompt($business, $keywords);
    
    try {
        $analysis = $this->gemini->analyze($prompt);
        return $this->processKeywordAnalysis($analysis);
    } catch (\Exception $e) {
        Log::error("Erro na análise de palavras-chave: " . $e->getMessage());
        return [];
    }
}

private function buildKeywordAnalysisPrompt($business, $keywords)
{
    return "Analise as seguintes palavras-chave encontradas para o negócio '{$business->name}' 
            do segmento '{$business->segment}':
            
            " . implode(", ", array_keys($keywords)) . "
            
            Por favor, identifique e retorne apenas as palavras-chave mais relevantes 
            que potenciais clientes usariam para encontrar este tipo de negócio,
            junto com uma estimativa de relevância (1-100).
            
            Formato da resposta:
            palavra_chave|relevância";
}

private function processKeywordAnalysis($analysis)
{
    $keywords = [];
    $lines = explode("\n", $analysis);
    
    foreach ($lines as $line) {
        if (strpos($line, '|') !== false) {
            list($keyword, $relevance) = explode('|', $line);
            $keywords[trim($keyword)] = (int)trim($relevance);
        }
    }
    
    return $keywords;
}

public function getCompetitorAnalysis($business, $analyticsData)
{
    try {
        // Search for competitors
        $competitors = $this->serper->searchCompetitors($business->name, $business->city);
        
        // Format competitor data for analysis
        $formattedCompetitors = [];
        foreach ($competitors as $competitor) {
            $formattedCompetitors[] = [
                'name' => $competitor['title'] ?? '',
                'description' => $competitor['snippet'] ?? '',
                'rating' => $competitor['rating'] ?? null,
                'reviews' => $competitor['reviews'] ?? 0,
            ];
        }

     // Generate analysis prompt
     $prompt = "Analise estes concorrentes para {$business->name} em {$business->city}:\n\n";

     foreach ($formattedCompetitors as $competitor) {
        $prompt .= "Concorrente: {$competitor['name']}\n";
        $prompt .= "Descrição: {$competitor['description']}\n";
        if ($competitor['rating']) {
            $prompt .= "Avaliação: {$competitor['rating']} ({$competitor['reviews']} avaliações)\n";
        }
        $prompt .= "\n";
    }

     // Get analysis from Gemini
     $analysis = $this->gemini->generateContent($prompt);

     // Return just the content string instead of an array
     return $analysis;

 } catch (\Exception $e) {
     \Log::error('Error generating competitor analysis: ' . $e->getMessage());
     return "Não foi possível gerar a análise de concorrentes no momento. Por favor, tente novamente mais tarde.";
 }
}
private function formatCompetitorAnalysis($analysis)
{
    // Divide a análise em seções
    $sections = preg_split('/([A-Z\s]+:)/', $analysis, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    
    $formattedSections = [];
    
    // Processa cada seção mantendo a formatação
    for ($i = 0; $i < count($sections) - 1; $i += 2) {
        $title = trim($sections[$i]);
        $content = trim($sections[$i + 1]);
        
        // Formata o conteúdo em linhas organizadas
        $contentLines = array_map('trim', explode("\n", $content));
        $contentLines = array_filter($contentLines, function($line) {
            return !empty($line);
        });
        
        $formattedSections[] = [
            'title' => $title,
            'content' => $contentLines
        ];
    }
    
    return $formattedSections;
}

public function generateContentSuggestions($business)
{
    try {
        // Buscar dados relevantes do negócio
        $metrics = [
            'views' => $business->analytics()->sum('views'),
            'engagement' => $business->analytics()->avg('engagement_rate'),
            'popular_times' => $business->analytics()->pluck('popular_times')->filter()
        ];

        // Criar prompt para o Gemini
        $prompt = "Gere sugestões de conteúdo para um negócio do segmento {$business->segment} " .
                 "considerando os seguintes dados:\n" .
                 "- Visualizações: {$metrics['views']}\n" .
                 "- Taxa de engajamento: {$metrics['engagement']}\n" .
                 "- Localização: {$business->city}, {$business->state}";

        // Gerar sugestões usando o Gemini
        $suggestions = $this->gemini->generateContent($prompt);

        // Formatar as sugestões
        return [
            'post_ideas' => [
                [
                    'title' => 'Conteúdo Engajador',
                    'description' => 'Posts que destacam os diferenciais do seu negócio',
                    'examples' => $this->formatPostExamples($suggestions['content'])
                ],
                [
                    'title' => 'Promoções Sazonais',
                    'description' => 'Ofertas baseadas na sazonalidade do seu segmento',
                    'examples' => $this->formatPromotionExamples($suggestions['content'])
                ]
            ],
            'best_times' => [
                'to_post' => $this->analyzeBestTimes($metrics['popular_times']),
                'explanation' => 'Horários com maior engajamento do público'
            ],
            'content_types' => [
                'recommended' => ['photos', 'videos', 'stories'],
                'priority' => 'photos'
            ]
        ];
    } catch (\Exception $e) {
        \Log::error('Erro ao gerar sugestões de conteúdo: ' . $e->getMessage());
        return [
            'error' => true,
            'message' => 'Não foi possível gerar sugestões de conteúdo'
        ];
    }
}

private function formatPostExamples($content)
{
    // Implementar lógica de formatação de exemplos de posts
    return array_slice(explode("\n", $content), 0, 3);
}

private function formatPromotionExamples($content)
{
    // Implementar lógica de formatação de exemplos de promoções
    return array_slice(explode("\n", $content), 3, 3);
}

private function analyzeBestTimes($popularTimes)
{
    // Implementar lógica para analisar melhores horários
    return [
        'weekdays' => ['10:00', '15:00', '19:00'],
        'weekends' => ['11:00', '16:00', '20:00']
    ];
}
}