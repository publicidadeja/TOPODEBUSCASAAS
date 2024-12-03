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

    private function extractSuggestions($content)
    {
        $suggestions = [];
        
        // Procurar por padrões de sugestões no texto
        if (preg_match_all('/\b(Sugestão|Melhoria|Recomendação):\s*([^\n]+)/i', $content, $matches)) {
            foreach ($matches[2] as $index => $suggestion) {
                $suggestions[] = [
                    'title' => 'Sugestão ' . ($index + 1),
                    'message' => trim($suggestion),
                    'action_type' => $this->determineActionType($suggestion),
                    'action_data' => $this->extractActionData($suggestion),
                    'priority' => $this->determinePriority($suggestion)
                ];
            }
        }
        
        return $suggestions ?: [
            [
                'title' => 'Melhoria de Fotos',
                'message' => 'Atualize as fotos do estabelecimento',
                'action_type' => 'update_photos',
                'action_data' => ['type' => 'exterior'],
                'priority' => 'high'
            ]
        ];
    }

    private function extractImportantDates($content)
    {
        $dates = [];
        
        // Procurar por datas e eventos no texto
        if (preg_match_all('/(\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})[:\s-]+([^\n]+)/', $content, $matches)) {
            foreach ($matches[1] as $index => $date) {
                $dates[] = [
                    'date' => $date,
                    'title' => trim($matches[2][$index]),
                    'description' => $this->extractDateDescription($content, $matches[2][$index])
                ];
            }
        }
        
        return $dates ?: [
            [
                'date' => '2024-03-01',
                'title' => 'Início da Temporada',
                'description' => 'Período importante para atualização de produtos'
            ]
        ];
    }

    private function extractPriorityActions($content)
    {
        $actions = [];
        
        // Procurar por ações prioritárias no texto
        if (preg_match_all('/\b(Ação Prioritária|Prioridade):\s*([^\n]+)/i', $content, $matches)) {
            foreach ($matches[2] as $action) {
                $actions[] = [
                    'action' => trim($action),
                    'priority' => $this->determinePriority($action),
                    'deadline' => $this->extractDeadline($action)
                ];
            }
        }
        
        return $actions ?: [
            [
                'action' => 'Atualizar horário de funcionamento',
                'priority' => 'high',
                'deadline' => '2024-02-15'
            ]
        ];
    }

    private function determineActionType($text)
    {
        $types = [
            'foto' => 'update_photos',
            'horário' => 'update_hours',
            'descrição' => 'update_description',
            'post' => 'create_post',
            'produto' => 'update_products',
            'preço' => 'update_prices',
            'contato' => 'update_contact'
        ];
        
        foreach ($types as $keyword => $type) {
            if (stripos($text, $keyword) !== false) {
                return $type;
            }
        }
        
        return 'general_action';
    }

    private function determinePriority($text)
    {
        if (stripos($text, 'urgente') !== false || stripos($text, 'imediato') !== false || stripos($text, 'crítico') !== false) {
            return 'high';
        }
        if (stripos($text, 'média') !== false || stripos($text, 'moderada') !== false) {
            return 'medium';
        }
        return 'low';
    }

    private function extractActionData($text)
    {
        $data = ['type' => 'general'];
        
        if (stripos($text, 'foto') !== false) {
            $data['type'] = 'photo';
            if (stripos($text, 'exterior') !== false) {
                $data['location'] = 'exterior';
            } elseif (stripos($text, 'interior') !== false) {
                $data['location'] = 'interior';
            }
        }
        
        return $data;
    }

    private function extractDateDescription($content, $title)
    {
        // Tenta encontrar uma descrição mais detalhada após o título
        $pattern = '/' . preg_quote($title, '/') . '[\s-]*([^\n]+)/i';
        if (preg_match($pattern, $content, $match)) {
            return trim($match[1]);
        }
        return '';
    }

    private function extractDeadline($text)
    {
        // Tenta encontrar uma data no formato DD/MM/YYYY ou YYYY-MM-DD
        if (preg_match('/(\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})/', $text, $match)) {
            return $match[1];
        }
        // Se não encontrar, retorna uma data padrão 15 dias no futuro
        return date('Y-m-d', strtotime('+15 days'));
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
        return trim(str_replace('*', '', $lines[0]));
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
        try {
            $serper = app(\App\Services\SerperService::class);
            // Validar dados de entrada
            if (!$business || !$analytics) {
                throw new \InvalidArgumentException('Dados do negócio ou analytics faltando');
            }

            // Instancia o SerperService
            $serper = app(SerperService::class);
            
            // Faz busca por concorrentes
            $searchQuery = "{$business->name} concorrentes {$business->segment} {$business->address}";
            $searchResults = $serper->search($searchQuery);
            
            // Prepara prompt com dados reais
            $prompt = $this->buildAnalysisPrompt($business, $analytics, $searchResults);
            
            // Chama API do Gemini
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
                return $this->parseAnalysisResponse($data);
            }

            Log::error('Erro na resposta do Gemini: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Erro ao analisar dados: ' . $e->getMessage());
            return null;
        }
    }

    private function buildAnalysisPrompt($business, $analytics, $searchResults)
    {
        $views = is_array($analytics['views']) ? array_sum($analytics['views']) : $analytics['views'];
        $clicks = is_array($analytics['clicks']) ? array_sum($analytics['clicks']) : $analytics['clicks'];
        $conversionRate = $analytics['currentConversion'] ?? 0;
        
        $viewsGrowth = $analytics['growth']['views'] ?? 0;
        $clicksGrowth = $analytics['growth']['clicks'] ?? 0;

        $competitorsInfo = "";
        if ($searchResults && isset($searchResults['organic'])) {
            $competitorsInfo = "\n\nConcorrentes encontrados:\n";
            foreach ($searchResults['organic'] as $competitor) {
                $competitorsInfo .= "- {$competitor['title']}\n";
            }
        }

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
        {$competitorsInfo}

        Por favor, forneça:
        1. Análise do desempenho atual
        2. Identificação de problemas
        3. Oportunidades de melhoria
        4. Recomendações específicas";
    }

    private function parseAnalysisResponse($data)
    {
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Resposta inválida da API');
        }
        
        $content = $data['candidates'][0]['content']['parts'][0]['text'];
        
        return [
            'analysis' => $content,
            'suggestions' => $this->extractSuggestions($content),
            'important_dates' => $this->extractImportantDates($content),
            'priority_actions' => $this->extractPriorityActions($content),
            'timestamp' => now() 
        ];
    }
}