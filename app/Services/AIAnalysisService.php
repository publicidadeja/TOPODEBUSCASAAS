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
    $metrics = [
        'views' => $business->analytics()->sum('views'),
        'clicks' => $business->analytics()->sum('clicks'),
        'calls' => $business->analytics()->sum('calls'),
        'reviews' => $business->reviews()->count(),
        'average_rating' => $business->reviews()->avg('rating')
    ];

    // Garanta que o retorno tenha a estrutura correta
    return [
        'performance' => [
            'type' => 'performance',
            'message' => 'Análise de performance do negócio'
        ],
        'opportunities' => [
            'type' => 'opportunity',
            'message' => 'Oportunidades identificadas'
        ],
        'alerts' => [
            'type' => 'alert',
            'message' => 'Alertas importantes'
        ]
    ];
}

    public function generateContentSuggestions($business)
    {
        $context = [
            'name' => $business->name,
            'segment' => $business->segment,
            'description' => $business->description,
            'recent_posts' => $business->posts()->latest()->take(5)->get()
        ];

        return $this->gemini->generateContentIdeas($context);
    }

    public function analyzeCompetitors($business)
    {
        $competitors = $this->serper->search("{$business->name} concorrentes {$business->segment} {$business->address}");
        return $this->gemini->analyzeCompetitors($competitors);
    }
}