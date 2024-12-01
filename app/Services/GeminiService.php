<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function generateContent($prompt)
{
    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiEndpoint . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'title' => $this->extractTitle($data),
                'content' => $this->extractContent($data)
            ];
        }

        Log::error('Erro na resposta da API Gemini', [
            'response' => $response->json(),
            'status' => $response->status()
        ]);

        return [
            'title' => 'Erro',
            'content' => 'Não foi possível gerar o conteúdo. Tente novamente.'
        ];
    } catch (\Exception $e) {
        Log::error('Erro ao gerar conteúdo com Gemini: ' . $e->getMessage());
        return [
            'title' => 'Erro',
            'content' => 'Ocorreu um erro ao processar sua solicitação.'
        ];
    }
}
public function generateResponse($prompt)
{
    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiEndpoint . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $this->extractContent($data);
        }

        Log::error('Erro na resposta da API Gemini', [
            'response' => $response->json(),
            'status' => $response->status()
        ]);

        return 'Não foi possível gerar uma resposta. Tente novamente.';
    } catch (\Exception $e) {
        Log::error('Erro ao gerar resposta com Gemini: ' . $e->getMessage());
        return 'Ocorreu um erro ao processar sua solicitação.';
    }
}

private function extractTitle($data)
{
    $content = $data['candidates'][0]['content']['parts'][0]['text'];
    $lines = explode("\n", $content);
    return trim(str_replace('*', '', $lines[0])); // Remove asteriscos da formatação
}

private function extractContent($data)
{
    return $data['candidates'][0]['content']['parts'][0]['text'];
}

public function generateSuggestions($context)
{
    $prompt = "Com base no seguinte contexto, gere 3 sugestões de melhoria:\n\n" . $context;
    $response = $this->generateContent($prompt);
    return explode("\n", $response['content']);
}

public function generatePostIdeas($topic)
{
    $prompt = "Gere 5 ideias de posts sobre: " . $topic;
    return $this->generateContent($prompt);
}

public function generateReviewResponse($review)
{
    $prompt = "Gere uma resposta profissional e empática para esta avaliação:\n\n" . $review;
    return $this->generateResponse($prompt);
}

public function analyzeBusinessData($business, $analytics)
{
    $prompt = $this->buildAnalysisPrompt($business, $analytics);
    
    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiEndpoint . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $this->extractAnalysis($data);
        }

        Log::error('Erro na análise do negócio via Gemini', [
            'business_id' => $business->id,
            'response' => $response->json()
        ]);

        return null;
    } catch (\Exception $e) {
        Log::error('Erro ao analisar negócio: ' . $e->getMessage());
        return null;
    }
}

private function buildAnalysisPrompt($business, $analytics)
{
    // Convert arrays to strings or get first value if array
    $views = is_array($analytics['views']) ? array_sum($analytics['views']) : $analytics['views'];
    $clicks = is_array($analytics['clicks']) ? array_sum($analytics['clicks']) : $analytics['clicks'];
    $conversionRate = $analytics['currentConversion'] ?? 0; // Use currentConversion instead of conversion_rate
    
    // Get growth values from the growth array
    $viewsGrowth = $analytics['growth']['views'] ?? 0;
    $clicksGrowth = $analytics['growth']['clicks'] ?? 0;

    return "Analise os seguintes dados do negócio e forneça insights e recomendações:

    Negócio: {$business->name}
    Segmento: {$business->segment}

    Métricas dos últimos 30 dias:
    - Visualizações: {$views}
    - Cliques: {$clicks}
    - Taxa de Conversão: {$conversionRate}%

    Tendências:
    - Crescimento de visualizações: {$viewsGrowth}%
    - Crescimento de cliques: {$clicksGrowth}%

    Por favor, forneça:
    1. Análise do desempenho atual
    2. Identificação de problemas
    3. Oportunidades de melhoria
    4. Recomendações específicas";
}

private function extractAnalysis($data)
{
    $content = $data['candidates'][0]['content']['parts'][0]['text'];
    
    // Estrutura a resposta em um formato mais útil
    return [
        'analysis' => $content,
        'timestamp' => now()
    ];
}
}