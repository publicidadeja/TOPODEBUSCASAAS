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
    // Se $content for um array, converte para string
    if (is_array($content)) {
        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
    }

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

    // Se não encontrou sugestões, retorna uma sugestão padrão
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
    
}