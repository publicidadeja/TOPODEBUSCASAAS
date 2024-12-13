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
        if (empty($this->apiKey)) {
            Log::warning('Gemini API key not configured');
        }
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
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }
    
        $suggestions = [];
        
        // Melhorar a expressão regular para capturar recomendações
        if (preg_match_all('/(?:Recomendação|Sugestão|Melhoria):\s*([^.!?\n]+[.!?])/i', $content, $matches)) {
            foreach ($matches[1] as $index => $suggestion) {
                $suggestions[] = [
                    'title' => 'Recomendação ' . ($index + 1),
                    'message' => trim($suggestion),
                    'action_type' => $this->determineActionType($suggestion),
                    'action_data' => $this->extractActionData($suggestion),
                    'priority' => $this->determinePriority($suggestion)
                ];
            }
        }
        
        // Só retorna a sugestão padrão se realmente não encontrar nada
        if (empty($suggestions) && strpos($content, 'error') === false) {
            // Tenta extrair qualquer conteúdo relevante
            $sentences = explode('.', $content);
            foreach ($sentences as $index => $sentence) {
                if (strlen(trim($sentence)) > 30) {
                    $suggestions[] = [
                        'title' => 'Análise ' . ($index + 1),
                        'message' => trim($sentence),
                        'action_type' => 'general',
                        'action_data' => [],
                        'priority' => 'medium'
                    ];
                }
            }
        }
        
        return $suggestions ?: [
            [
                'title' => 'Melhoria de Fotos',
                'message' => 'Considere atualizar as fotos do seu negócio para melhor visibilidade',
                'action_type' => 'update_photos',
                'action_data' => [],
                'priority' => 'medium'
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

    public function analyzeBusinessData($business, $analytics, $competitors = null)
{
    $prompt = $this->buildAnalysisPrompt($business, $analytics, $competitors);
    
    try {
        $response = $this->generateContent($prompt);
        
        return [
            'suggestions' => $this->extractSuggestions($response),
            'analysis' => $response
        ];
    } catch (\Exception $e) {
        Log::error('Erro na análise do Gemini: ' . $e->getMessage());
        return null;
    }
}

private function buildAnalysisPrompt($business, $analytics, $competitors)
{
    return "Analise os seguintes dados e sugira melhorias específicas:
    Negócio: {$business->name}
    Segmento: {$business->segment}
    Dados Analytics: " . json_encode($analytics) . "
    Dados Concorrentes: " . json_encode($competitors);
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
/**
 * Analisa os dados do negócio usando o Gemini API
 * 
 * @param string $prompt
 * @return array
 */
public function analyze($prompt)
{
    $prompt = "Baseado nos seguintes dados, forneça recomendações estratégicas específicas:\n" . $prompt;
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
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'performance' => $this->extractPerformanceInsight($data['candidates'][0]['content']['parts'][0]['text']),
                    'opportunities' => $this->extractOpportunityInsight($data['candidates'][0]['content']['parts'][0]['text']),
                    'alerts' => $this->extractAlertInsight($data['candidates'][0]['content']['parts'][0]['text'])
                ];
            }
        }

        Log::error('Erro na requisição Gemini:', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return [
            'performance' => [
                'type' => 'performance',
                'message' => 'Não foi possível gerar análise de performance no momento.'
            ],
            'opportunities' => [
                'type' => 'opportunity',
                'message' => 'Não foi possível identificar oportunidades no momento.'
            ],
            'alerts' => [
                'type' => 'alert',
                'message' => 'Não foi possível gerar alertas no momento.'
            ]
        ];

    } catch (\Exception $e) {
        Log::error('Erro ao analisar dados com Gemini: ' . $e->getMessage());
        
        return [
            'performance' => [
                'type' => 'performance',
                'message' => 'Erro ao processar análise de performance.'
            ],
            'opportunities' => [
                'type' => 'opportunity',
                'message' => 'Erro ao processar análise de oportunidades.'
            ],
            'alerts' => [
                'type' => 'alert',
                'message' => 'Erro ao processar análise de alertas.'
            ]
        ];
    }
}

/**
 * Extrai insights de performance da análise
 */
private function extractPerformanceInsight($analysis)
{
    // Implementar lógica de extração de performance
    return [
        'type' => 'performance',
        'message' => $this->extractSection($analysis, 'Performance')
    ];
}

/**
 * Extrai insights de oportunidades da análise
 */
private function extractOpportunityInsight($analysis)
{
    // Implementar lógica de extração de oportunidades
    return [
        'type' => 'opportunity',
        'message' => $this->extractSection($analysis, 'Opportunities')
    ];
}

/**
 * Extrai alertas da análise
 */
private function extractAlertInsight($analysis)
{
    // Implementar lógica de extração de alertas
    return [
        'type' => 'alert',
        'message' => $this->extractSection($analysis, 'Alerts')
    ];
}

/**
 * Extrai uma seção específica da análise
 */
private function extractSection($analysis, $sectionName)
{
    // Verifica se a análise é uma string
    if (!is_string($analysis)) {
        $analysis = json_encode($analysis, JSON_UNESCAPED_UNICODE);
    }
    
    // Tenta encontrar a seção no formato atual
    $pattern = "/#$sectionName#\s*(.*?)\s*(?=#|$)/s";
    if (preg_match($pattern, $analysis, $matches)) {
        return trim($matches[1]);
    }
    
    // Tenta encontrar a seção em formato alternativo
    $pattern = "\"$sectionName\":\s*\"(.*?)\"";
    if (preg_match($pattern, $analysis, $matches)) {
        return trim($matches[1]);
    }
    
    return null;
}

// Em GeminiService.php
public function testConnection()
{
    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($this->apiEndpoint . '?key=' . $this->apiKey);

        Log::info('Teste de conexão Gemini:', [
            'status' => $response->status(),
            'has_api_key' => !empty($this->apiKey),
            'api_key_length' => strlen($this->apiKey)
        ]);

        return $response->successful();
    } catch (\Exception $e) {
        Log::error('Erro no teste de conexão Gemini: ' . $e->getMessage());
        return false;
    }
}

// Em um comando artisan ou controller
public function checkGeminiSetup(GeminiService $gemini)
{
    if ($gemini->testConnection()) {
        $this->info('Conexão com Gemini API estabelecida com sucesso!');
    } else {
        $this->error('Falha na conexão com Gemini API. Verifique suas credenciais.');
    }
}

// Em GeminiService.php
public function analyzeMarketData($business, $competitors)
{
    try {
        // Primeiro, vamos garantir que temos um array de competidores
        $competitorsData = [];
        
        // Verifica se $competitors é uma Collection ou um array
        if ($competitors instanceof \Illuminate\Support\Collection) {
            $competitorsData = $competitors->map(function ($competitor) {
                return [
                    'name' => $competitor['title'] ?? 'Nome não disponível',
                    'rating' => $competitor['rating'] ?? 0,
                    'reviews_count' => $competitor['reviews'] ?? 0,
                    'location' => $competitor['location'] ?? 'Localização não disponível'
                ];
            })->toArray();
        } elseif (is_array($competitors)) {
            foreach ($competitors as $competitor) {
                $competitorsData[] = [
                    'name' => $competitor['title'] ?? 'Nome não disponível',
                    'rating' => $competitor['rating'] ?? 0,
                    'reviews_count' => $competitor['reviews'] ?? 0,
                    'location' => $competitor['location'] ?? 'Localização não disponível'
                ];
            }
        }

        $prompt = "Analise os seguintes dados e forneça uma análise de mercado detalhada em português:

            Negócio Principal:
            Nome: {$business->name}
            Segmento: {$business->segment}
            Descrição: {$business->description}
            
            Dados dos Concorrentes:
            " . json_encode($competitorsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "
    
            Forneça uma análise estruturada no seguinte formato:
    
            1. Visão geral do mercado:
               [Análise geral do mercado]
    
            2. Análise detalhada dos concorrentes:
               • [Ponto específico sobre concorrente 1]
               • [Ponto específico sobre concorrente 2]
               • [Análise comparativa]
               • [Posicionamento no mercado]
    
            3. Oportunidades identificadas:
               [Lista de oportunidades]
    
            4. Recomendações estratégicas:
               [Lista de recomendações]";
    
            $response = $this->generateResponse($prompt);
            $sections = $this->extractMarketAnalysisSections($response);
    
            return [
                'market_overview' => $sections['market_overview'] ?? 'Análise de mercado não disponível',
                'competitor_analysis' => $sections['competitor_analysis'] ?? 'Análise de concorrentes não disponível',
                'opportunities' => $sections['opportunities'] ?? 'Oportunidades não identificadas',
                'recommendations' => $sections['recommendations'] ?? 'Recomendações não disponíveis'
            ];
    
        } catch (\Exception $e) {
            Log::error('Erro na análise de mercado: ' . $e->getMessage());
            return [
                'market_overview' => 'Erro ao gerar análise de mercado',
                'competitor_analysis' => 'Erro ao analisar concorrentes',
                'opportunities' => 'Erro ao identificar oportunidades',
                'recommendations' => 'Erro ao gerar recomendações'
            ];
        }
    }

private function extractMarketAnalysisSections($content)
{
    $sections = [];
    
    // Padrões atualizados para melhor extração
    $patterns = [
        'market_overview' => '/(?:1\.|Visão geral do mercado:?)(.*?)(?=(?:2\.|Análise|$))/is',
        'competitor_analysis' => '/(?:2\.|Análise detalhada dos concorrentes:?)(.*?)(?=(?:3\.|Oportunidades|$))/is',
        'opportunities' => '/(?:3\.|Oportunidades identificadas:?)(.*?)(?=(?:4\.|Recomendações|$))/is',
        'recommendations' => '/(?:4\.|Recomendações estratégicas:?)(.*?)(?=$)/is'
    ];

    foreach ($patterns as $key => $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            // Formata o texto mantendo a estrutura de lista
            $text = trim($matches[1]);
            
            // Converte marcadores de lista em HTML
            $text = preg_replace('/^\s*[-•]\s*/m', '<li>', $text);
            $text = preg_replace('/(?<=<li>)(.*?)(?=(?:<li>|$))/s', '$1</li>', $text);
            
            if (strpos($text, '<li>') !== false) {
                $text = '<ul class="list-disc list-inside space-y-2">' . $text . '</ul>';
            }
            
            $sections[$key] = $text;
        }
    }

    return $sections;
}

private function cleanAnalysisText($text)
{
    // Remove caracteres especiais e formata o texto
    $text = preg_replace('/[\n\r]+/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    // Remove marcadores numéricos no início
    $text = preg_replace('/^\d+\.\s*/', '', $text);
    
    return $text;
}

public function testMarketAnalysis($business, $competitors)
{
    try {
        $result = $this->analyzeMarketData($business, $competitors);
        
        Log::info('Teste de análise de mercado:', [
            'business' => $business->name,
            'competitors_count' => count($competitors),
            'result' => $result
        ]);

        return [
            'success' => true,
            'data' => $result
        ];
    } catch (\Exception $e) {
        Log::error('Erro no teste de análise de mercado: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// No GeminiService ou Controller
private function formatAnalysisText($text) {
    // Remove espaços extras
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Converte marcadores em HTML
    $text = preg_replace('/•\s*/', "\n• ", $text);
    
    // Adiciona quebras de linha entre parágrafos
    $text = preg_replace('/\.\s+(?=[A-Z])/', ".\n\n", $text);
    
    return trim($text);
}
    
}