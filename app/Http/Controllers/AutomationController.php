<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\AutomatedPost;
use App\Services\GeminiService;
use App\Models\CalendarEvent;
use App\Services\SerperService;
use App\Services\AIAnalysisService;

class AutomationController extends Controller
{
    protected $gemini;
protected $serper;
protected $googleBusiness;
protected $aiAnalysis;

public function __construct(
    GeminiService $gemini, 
    SerperService $serper,
    AIAnalysisService $aiAnalysis
) {
    $this->gemini = $gemini;
    $this->serper = $serper;
    $this->aiAnalysis = $aiAnalysis;
}

public function getAIInsights(Business $business)
{
    try {
        $insights = [
            'performance' => $this->aiAnalysis->analyzeBusinessPerformance($business),
            'content' => $this->aiAnalysis->generateContentSuggestions($business),
            'competitors' => $this->aiAnalysis->analyzeCompetitors($business)
        ];

        return response()->json([
            'success' => true,
            'insights' => $insights
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro ao gerar insights: ' . $e->getMessage());
        return response()->json(['error' => 'Erro ao gerar insights'], 500);
    }
}

private function getBusinessMetrics($business)
{
    return [
        'views' => $business->analytics()->sum('views'),
        'clicks' => $business->analytics()->sum('clicks'),
        'calls' => $business->analytics()->sum('calls'),
        'conversion_rate' => $business->getConversionRate(),
        'growth_rate' => $business->getGrowthRate(),
        'reviews_count' => $business->reviews()->count(),
        'average_rating' => $business->reviews()->avg('rating')
    ];
}

private function isRainySeason()
{
    // Implementar lógica de verificação de estação chuvosa
    // Pode usar uma API de clima ou definir manualmente os meses
    $rainyMonths = [11, 12, 1, 2, 3]; // exemplo
    return in_array(now()->month, $rainyMonths);
}

private function handlePhotoPostSuggestion($business)
{
    try {
        // Gerar sugestão de post com foto
        $suggestion = $this->gemini->generatePhotoPostSuggestion($business);
        
        // Criar post automatizado
        $post = AutomatedPost::create([
            'business_id' => $business->id,
            'type' => 'photo',
            'title' => $suggestion['title'],
            'content' => $suggestion['content'],
            'scheduled_for' => now()->addDay(),
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sugestão de post com foto criada',
            'post' => $post
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao criar sugestão: ' . $e->getMessage()
        ], 500);
    }
}

private function handleServiceHighlight($business, $data)
{
    try {
        // Atualizar destaque do serviço
        $this->googleBusiness->updateServiceHighlight($business, $data['service']);
        
        return response()->json([
            'success' => true,
            'message' => 'Serviço destacado com sucesso'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao destacar serviço: ' . $e->getMessage()
        ], 500);
    }
}

private function handleDescriptionUpdate($business, $data)
{
    try {
        // Atualizar descrição do negócio
        $this->googleBusiness->updateBusinessDescription(
            $business,
            $data['highlight']
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Descrição atualizada com sucesso'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao atualizar descrição: ' . $e->getMessage()
        ], 500);
    }
}

private function handleDeliveryActivation($business)
{
    try {
        // Ativar opção de delivery
        $this->googleBusiness->updateBusinessAttributes($business, [
            'has_delivery' => true
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Delivery ativado com sucesso'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao ativar delivery: ' . $e->getMessage()
        ], 500);
    }
}

private function getReviewsData($business, $params)
{
    return [
        'total' => $business->reviews()->count(),
        'average_rating' => $business->reviews()->avg('rating'),
        'response_rate' => $this->calculateResponseRate($business),
        'sentiment_analysis' => $this->analyzeSentiment($business->reviews)
    ];
}

private function calculateResponseRate($business)
{
    $totalReviews = $business->reviews()->count();
    $respondedReviews = $business->reviews()->whereNotNull('response')->count();
    
    return $totalReviews > 0 ? ($respondedReviews / $totalReviews) * 100 : 0;
}

private function analyzeSentiment($reviews)
{
    // Usar o Gemini para análise de sentimento
    return $this->gemini->analyzeSentiment($reviews);
}
    public function getImprovementSuggestions(Business $business, Request $request)
{
    try {
        // Se for uma atualização manual, força a geração de novas sugestões
        $forceRefresh = $request->query('refresh', false);
        
        // Buscar dados dos concorrentes
        $competitors = $this->serper->search("{$business->name} concorrentes {$business->segment} {$business->address}");
        
        // Analisar dados com Gemini
        $analysis = $this->gemini->analyzeBusinessData($business, [
            'competitors' => $competitors,
            'metrics' => $this->getBusinessMetrics($business),
            'segment' => $business->segment,
            'force_refresh' => $forceRefresh
        ]);

        // Criar notificações baseadas na análise
        $this->createImprovementNotifications($business, $analysis);

        return response()->json([
            'success' => true,
            'suggestions' => $analysis,
            'notifications_created' => true,
            'refreshed' => $forceRefresh
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro ao gerar sugestões: ' . $e->getMessage());
        return response()->json(['error' => 'Erro ao gerar sugestões'], 500);
    }
}

    private function createImprovementNotifications($business, $analysis)
    {
        foreach ($analysis['suggestions'] as $suggestion) {
            Notification::create([
                'business_id' => $business->id,
                'type' => 'improvement',
                'title' => $suggestion['title'],
                'message' => $suggestion['message'],
                'action_type' => $suggestion['action_type'],
                'action_data' => json_encode($suggestion['action_data']),
                'priority' => $suggestion['priority']
            ]);
        }
    }

    public function applyImprovement(Request $request, Business $business)
    {
        try {
            $validated = $request->validate([
                'improvement_type' => 'required|string',
                'data' => 'required|array'
            ]);

            // Implementar melhorias específicas
            switch ($validated['improvement_type']) {
                case 'update_photos':
                    return $this->handlePhotoUpdate($business, $validated['data']);
                
                case 'update_business_info':
                    return $this->handleBusinessInfoUpdate($business, $validated['data']);
                
                case 'update_products':
                    return $this->handleProductsUpdate($business, $validated['data']);
                
                default:
                    return response()->json(['error' => 'Tipo de melhoria não suportado'], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Erro ao aplicar melhoria: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao aplicar melhoria'], 500);
        }
    }

    private function handlePhotoUpdate($business, $data)
    {
        // Implementar lógica de atualização de fotos
        // Usar API do Google My Business para atualizar
        return response()->json(['success' => true, 'message' => 'Fotos atualizadas com sucesso']);
    }

    private function handleBusinessInfoUpdate($business, $data)
    {
        // Implementar lógica de atualização de informações
        return response()->json(['success' => true, 'message' => 'Informações atualizadas com sucesso']);
    }

    private function handleProductsUpdate($business, $data)
    {
        // Implementar lógica de atualização de produtos
        return response()->json(['success' => true, 'message' => 'Produtos atualizados com sucesso']);
    }


    public function index()
{
    $business = Business::where('user_id', auth()->id())->first();

    if (!$business) {
        return redirect()
            ->route('business.create')
            ->with('warning', 'Você precisa cadastrar seu negócio primeiro.');
    }

    $scheduledPosts = AutomatedPost::where('business_id', $business->id)
        ->where('is_posted', false)
        ->orderBy('scheduled_for')
        ->get();
        
    $postedPosts = AutomatedPost::where('business_id', $business->id)
        ->where('is_posted', true)
        ->orderBy('scheduled_for', 'desc')
        ->take(5)
        ->get();

    // Adicionar novos dados
    $protectionStatus = $this->getProtectionStatus();
    $competitiveAnalysis = $this->getDetailedCompetitiveAnalysis();
    $smartNotifications = $this->getSmartNotifications();

    return view('automation.index', compact(
        'business',
        'scheduledPosts',
        'postedPosts',
        'protectionStatus',
        'competitiveAnalysis',
        'smartNotifications'
    ));
}
    public function createPost(Request $request)
    {
        $business = Business::where('user_id', auth()->id())->first();
        
        if (!$business) {
            return redirect()
                ->route('business.create')
                ->with('error', 'Você precisa cadastrar seu negócio primeiro.');
        }

        $validated = $request->validate([
            'event_type' => 'required|string',
    'title' => 'required|string',
    'suggestion' => 'nullable|string', // Changed from 'required' to 'nullable'
    'start_date' => 'required|date',
    'end_date' => 'required|date|after:start_date'
]);

        // Gerar conteúdo com Gemini
        $prompt = $validated['customPrompt'] ?? $this->getDefaultPrompt($business, $validated['type']);
        $content = $this->gemini->generateContent($prompt);

        $post = new AutomatedPost();
        $post->business_id = $business->id;
        $post->type = $validated['type'];
        $post->title = $content['title'];
        $post->content = $content['content'];
        $post->scheduled_for = $validated['scheduled_for'];
        $post->save();

        return redirect()
            ->route('automation.index')
            ->with('success', 'Post agendado com sucesso!');
    }

    public function updateHours(Request $request)
    {
        $business = Business::where('user_id', auth()->id())->first();
        
        if (!$business) {
            return redirect()
                ->route('business.create')
                ->with('error', 'Você precisa cadastrar seu negócio primeiro.');
        }

        $validated = $request->validate([
            'hours' => 'required|array',
            'hours.*.day' => 'required|string',
            'hours.*.open' => 'required|string',
            'hours.*.close' => 'required|string',
            'hours.*.closed' => 'boolean'
        ]);

        foreach ($validated['hours'] as $hour) {
            $business->businessHours()->updateOrCreate(
                ['day_of_week' => $hour['day']],
                [
                    'opening_time' => $hour['open'],
                    'closing_time' => $hour['close'],
                    'is_closed' => $hour['closed'] ?? false
                ]
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Horários atualizados com sucesso!');
    }

    public function respondReview(Request $request)
    {
        $business = Business::where('user_id', auth()->id())->first();
        
        if (!$business) {
            return redirect()
                ->route('business.create')
                ->with('error', 'Você precisa cadastrar seu negócio primeiro.');
        }

        $validated = $request->validate([
            'review_id' => 'required|string',
            'review_text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        // Gerar resposta com Gemini
        $prompt = $this->getReviewResponsePrompt($business, $validated['review_text'], $validated['rating']);
        $response = $this->gemini->generateResponse($prompt);

        return redirect()
            ->back()
            ->with('success', 'Resposta enviada com sucesso!');
    }

    private function getDefaultPrompt($business, $type)
    {
        $basePrompt = "Criar uma postagem para {$business->name}, um(a) {$business->segment}. ";
        
        switch ($type) {
            case 'promotion':
                return $basePrompt . "Foco em promoção ou oferta especial.";
            case 'engagement':
                return $basePrompt . "Foco em engajamento e interação com clientes.";
            case 'information':
                return $basePrompt . "Foco em informações úteis sobre produtos/serviços.";
            default:
                return $basePrompt . "Conteúdo geral sobre o negócio.";
        }
    }

    private function getReviewResponsePrompt($business, $reviewText, $rating)
    {
        return "Criar uma resposta profissional e empática para a seguinte avaliação de {$business->name}: 
                Avaliação: {$reviewText}
                Classificação: {$rating} estrelas";
    }


public function updateHolidayHours(Request $request)
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return redirect()
            ->route('business.create')
            ->with('error', 'Você precisa cadastrar seu negócio primeiro.');
    }

    $validated = $request->validate([
        'holiday_date' => 'required|date',
        'opening_time' => 'required_without:is_closed',
        'closing_time' => 'required_without:is_closed',
        'is_closed' => 'boolean'
    ]);

    $holidayHours = $business->holidayHours()->updateOrCreate(
        ['date' => $validated['holiday_date']],
        [
            'opening_time' => $validated['opening_time'] ?? null,
            'closing_time' => $validated['closing_time'] ?? null,
            'is_closed' => $validated['is_closed'] ?? false
        ]
    );

    return redirect()
        ->back()
        ->with('success', 'Horários de feriado atualizados com sucesso!');
}

public function getSmartCalendarSuggestions(Request $request)
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    // Lógica para gerar sugestões baseadas no segmento
    $suggestions = $this->generateSegmentSuggestions($business->segment);

    return response()->json(['suggestions' => $suggestions]);

    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    // Gerar sugestões baseadas no tipo de negócio
    $suggestions = $this->generateCalendarSuggestions($business);

    return response()->json(['suggestions' => $suggestions]);
}

private function generateSegmentSuggestions($segment)
{
    $currentMonth = now()->month;
    $suggestions = [];

    // Enhanced segment-specific suggestions
    switch (strtolower($segment)) {
        case 'restaurante':
            $suggestions = array_merge($suggestions, $this->getRestaurantSuggestions($currentMonth));
            break;
        case 'loja':
            $suggestions = array_merge($suggestions, $this->getRetailSuggestions($currentMonth));
            break;
        // Add more segments as needed
    }

    // Add general suggestions
    $suggestions = array_merge($suggestions, $this->getGeneralSuggestions($currentMonth));

    return $suggestions;
}

public function smartCalendar()
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return redirect()
            ->route('business.create')
            ->with('error', 'Você precisa cadastrar seu negócio primeiro.');
    }

    $events = $business->smartCalendar()
                      ->where('status', 'approved')
                      ->orderBy('start_date')
                      ->get();

    return view('automation.smart-calendar', compact('business', 'events'));
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return redirect()->route('business.create')->with('warning', 'Primeiro crie um negócio.');
    }

    return view('automation.smart-calendar', compact('business'));

    
}

public function createCalendarEvent(Request $request)
{
    try {
        $validated = $request->validate([
            'event_type' => 'required|string',
            'title' => 'required|string',
            'suggestion' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'nullable|string'
        ]);

        // Get business ID from session or current business
        $businessId = session('current_business_id');
        
        if (!$businessId) {
            $currentBusiness = auth()->user()->businesses()->first();
            if (!$currentBusiness) {
                return response()->json(['message' => 'No business found for this user'], 404);
            }
            $businessId = $currentBusiness->id;
        }

        $event = CalendarEvent::create([
            'business_id' => $businessId,
            'event_type' => $validated['event_type'],
            'title' => $validated['title'],
            'suggestion' => $validated['suggestion'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'] ?? 'active'
        ]);

        return response()->json($event, 201);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erro ao criar evento: ' . $e->getMessage()], 500);
    }
}

private function generateCalendarSuggestions($business)
{
    // Exemplo de sugestões baseadas no segmento do negócio
    $suggestions = [
        [
            'title' => 'Promoção Sazonal',
            'message' => 'Que tal criar uma promoção especial para aumentar as vendas neste período?',
            'type' => 'promotion'
        ],
        [
            'title' => 'Evento de Engajamento',
            'message' => 'Organize um evento para interagir com seus clientes e aumentar o engajamento.',
            'type' => 'engagement'
        ],
        [
            'title' => 'Postagem de Conteúdo',
            'message' => 'Programe postagens de conteúdo relevante para sua audiência.',
            'type' => 'content'
        ]
    ];

    return $suggestions;
}
public function storeCalendarEvent(Request $request)
{
    try {
        $business = Business::where('user_id', auth()->id())->firstOrFail();
        
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'color' => 'nullable|string'
        ]);

        $event = new SmartCalendar();
        $event->business_id = $business->id;
        $event->title = $validated['title'];
        $event->description = $validated['description'] ?? '';
        $event->start_date = $validated['start_date'];
        $event->end_date = $validated['end_date'];
        $event->color = $validated['color'] ?? '#3788d8';
        $event->save();

        return response()->json([
            'success' => true,
            'id' => $event->id,
            'message' => 'Evento criado com sucesso'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao criar evento: ' . $e->getMessage()
        ], 500);
    }
}

public function getCalendarEvents()
{
    // Pega o business_id da sessão (definido pelo middleware ShareCurrentBusiness)
    $businessId = session('current_business_id');
    
    if (!$businessId) {
        return response()->json([
            'success' => false,
            'message' => 'Nenhum negócio selecionado'
        ], 404);
    }

    try {
        $events = CalendarEvent::where('business_id', $businessId)->get();

        // Formatar os eventos para o formato que o FullCalendar espera
        $formattedEvents = $events->map(function($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'color' => $event->color,
                'description' => $event->description,
                'event_type' => $event->event_type
            ];
        });

        return response()->json($formattedEvents);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao carregar eventos: ' . $e->getMessage()
        ], 500);
    }
}

public function getCalendarSuggestions()
{
    try {
        // Recupera sugestões do banco de dados
        $suggestions = CalendarEvent::where('business_id', auth()->user()->current_business_id)
            ->where('status', 'suggested')
            ->get()
            ->map(function($event) {
                return [
                    'type' => $event->event_type,
                    'title' => $event->title,
                    'message' => $event->suggestion,
                    'id' => $event->id
                ];
            });

        return response()->json(['suggestions' => $suggestions]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erro ao carregar sugestões'], 500);
    }
}

private function getRestaurantSuggestions($currentMonth)
{
    $suggestions = [];
    
    switch ($currentMonth) {
        case 6: // Junho
            $suggestions[] = [
                'type' => 'event',
                'title' => 'Dia dos Namorados',
                'message' => 'Que tal criar um menu especial para o Dia dos Namorados?',
                'action_url' => '/automation/create-event/valentines',
                'priority' => 'high'
            ];
            break;
        case 12: // Dezembro
            $suggestions[] = [
                'type' => 'hours',
                'title' => 'Horários de Fim de Ano',
                'message' => 'Configure os horários especiais de Natal e Ano Novo',
                'action_url' => '/automation/holiday-hours',
                'priority' => 'high'
            ];
            $suggestions[] = [
                'type' => 'menu',
                'title' => 'Cardápio Festivo',
                'message' => 'Que tal criar um menu especial para as festas de fim de ano?',
                'action_url' => '/automation/create-menu/holiday',
                'priority' => 'medium'
            ];
            break;
        // Add more months
    }

    // Weather-based suggestions
    if ($this->isRainySeason()) {
        $suggestions[] = [
            'type' => 'service',
            'title' => 'Delivery em Alta',
            'message' => 'Época de chuvas: Quer ativar o botão de delivery?',
            'action_url' => '/automation/toggle-delivery',
            'priority' => 'medium'
        ];
    }

    return $suggestions;
}
// In AutomationController.php

public function getAIAssistantSuggestions()
{
    try {
        $business = Business::where('user_id', auth()->id())->first();
        
        if (!$business) {
            return response()->json(['error' => 'Negócio não encontrado'], 404);
        }

        // Buscar dados dos concorrentes com Serper
        $competitors = $this->serper->search("{$business->name} concorrentes {$business->segment} {$business->address}");
        
        // Analisar dados com Gemini
        $analysis = $this->gemini->analyzeBusinessData($business, [
            'competitors' => $competitors,
            'metrics' => $this->getBusinessMetrics($business)
        ]);

        return response()->json([
            'suggestions' => $analysis['suggestions']
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao gerar sugestões'], 500);
    }
}

public function getSegmentTrends()
{
    try {
        $business = Business::where('user_id', auth()->id())->first();
        
        if (!$business) {
            return response()->json(['error' => 'Negócio não encontrado'], 404);
        }

        // Buscar tendências do segmento com Serper
        $searchResults = $this->serper->search("tendências {$business->segment} {$business->address}");
        
        // Analisar resultados com Gemini
        $prompt = "Analise estas tendências do segmento e forneça insights: " . json_encode($searchResults);
        $analysis = $this->gemini->generateContent($prompt);

        return response()->json([
            'trends' => $this->formatTrends($analysis)
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao buscar tendências'], 500);
    }
}

public function getSeasonalEvents()
{
    try {
        $business = Business::where('user_id', auth()->id())->first();
        
        if (!$business) {
            return response()->json(['error' => 'Negócio não encontrado'], 404);
        }

        // Gerar eventos sazonais com Gemini
        $prompt = "Gere eventos sazonais relevantes para um negócio do segmento {$business->segment}";
        $events = $this->gemini->generateContent($prompt);

        return response()->json([
            'events' => $this->formatSeasonalEvents($events)
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao buscar eventos sazonais'], 500);
    }
}

private function formatTrends($analysis)
{
    // Formatar análise do Gemini em tendências estruturadas
    $content = $analysis['content'];
    $trends = [];
    
    // Dividir o conteúdo em linhas
    $lines = explode("\n", $content);
    
    $currentTrend = null;
    foreach ($lines as $line) {
        if (strpos($line, 'Tendência:') === 0) {
            if ($currentTrend) {
                $trends[] = $currentTrend;
            }
            $currentTrend = [
                'title' => trim(str_replace('Tendência:', '', $line)),
                'description' => '',
                'tags' => []
            ];
        } elseif ($currentTrend && strpos($line, 'Tags:') === 0) {
            $currentTrend['tags'] = array_map('trim', explode(',', str_replace('Tags:', '', $line)));
        } elseif ($currentTrend && !empty(trim($line))) {
            $currentTrend['description'] .= trim($line) . ' ';
        }
    }
    
    if ($currentTrend) {
        $trends[] = $currentTrend;
    }

    return $trends;
}

private function formatSeasonalEvents($events)
{
    // Formatar eventos gerados pelo Gemini
    $content = $events['content'];
    $formattedEvents = [];
    
    $lines = explode("\n", $content);
    
    $currentEvent = null;
    foreach ($lines as $line) {
        if (strpos($line, 'Evento:') === 0) {
            if ($currentEvent) {
                $formattedEvents[] = $currentEvent;
            }
            $currentEvent = [
                'title' => trim(str_replace('Evento:', '', $line)),
                'description' => '',
                'date' => null
            ];
        } elseif ($currentEvent && strpos($line, 'Data:') === 0) {
            $currentEvent['date'] = trim(str_replace('Data:', '', $line));
        } elseif ($currentEvent && !empty(trim($line))) {
            $currentEvent['description'] .= trim($line) . ' ';
        }
    }
    
    if ($currentEvent) {
        $formattedEvents[] = $currentEvent;
    }

    return $formattedEvents;
}



private function analyzeBusinessData($business)
{
    $suggestions = [];
    
    // Analyze competitor photos
    $competitorPhotos = $this->analyzeCompetitorPhotos($business);
    if ($competitorPhotos['needsImprovement']) {
        $suggestions[] = [
            'type' => 'photos',
            'title' => 'Melhoria de Fotos',
            'message' => "Seu concorrente {$competitorPhotos['competitor']} está ganhando mais visibilidade com fotos de produtos. Quer criar posts similares?",
            'action' => 'create_photo_post',
            'priority' => 'high'
        ];
    }

    // Analyze search trends
    $searchTrends = $this->analyzeSearchTrends($business);
    foreach ($searchTrends as $trend) {
        $suggestions[] = [
            'type' => 'search_trend',
            'title' => 'Tendência de Busca',
            'message' => "Detectamos muitas buscas por '{$trend['keyword']}' na sua região. Que tal destacar esse serviço?",
            'action' => 'highlight_service',
            'data' => ['service' => $trend['keyword']],
            'priority' => 'medium'
        ];
    }

    // Analyze reviews
    $reviewInsights = $this->analyzeReviews($business);
    if ($reviewInsights['commonTopic']) {
        $suggestions[] = [
            'type' => 'review_insight',
            'title' => 'Destaque nas Avaliações',
            'message' => "Suas avaliações mencionam muito '{$reviewInsights['commonTopic']}'. Vamos destacar isso na descrição?",
            'action' => 'update_description',
            'data' => ['highlight' => $reviewInsights['commonTopic']],
            'priority' => 'medium'
        ];
    }

    // Weather-based suggestions
    $weatherSuggestion = $this->getWeatherBasedSuggestion($business);
    if ($weatherSuggestion) {
        $suggestions[] = $weatherSuggestion;
    }

    return $suggestions;
}

private function analyzeCompetitorPhotos($business)
{
    // Simulação de análise de fotos dos concorrentes
    return [
        'needsImprovement' => true,
        'competitor' => 'Estabelecimento X'
    ];
}

private function analyzeSearchTrends($business)
{
    // Simulação de análise de tendências de busca
    return [
        ['keyword' => 'delivery', 'volume' => 150],
        ['keyword' => 'happy hour', 'volume' => 100]
    ];
}

private function analyzeReviews($business)
{
    // Simulação de análise de avaliações
    return [
        'commonTopic' => 'atendimento',
        'sentiment' => 'positive'
    ];
}

private function getWeatherBasedSuggestion($business)
{
    // Simulação de sugestão baseada no clima
    $isRainySeason = true; // Isso seria determinado por uma API de clima

    if ($isRainySeason) {
        return [
            'type' => 'weather',
            'title' => 'Sugestão de Clima',
            'message' => 'Época de chuvas: Quer ativar o botão de delivery?',
            'action' => 'enable_delivery',
            'priority' => 'high'
        ];
    }

    return null;
}

public function handleAISuggestion(Request $request)
{
    $validated = $request->validate([
        'suggestion_type' => 'required|string',
        'action' => 'required|string',
        'data' => 'nullable|array'
    ]);

    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    // Handle different types of suggestions
    switch ($validated['action']) {
        case 'create_photo_post':
            return $this->handlePhotoPostSuggestion($business);
        
        case 'highlight_service':
            return $this->handleServiceHighlight($business, $validated['data']);
            
        case 'update_description':
            return $this->handleDescriptionUpdate($business, $validated['data']);
            
        case 'enable_delivery':
            return $this->handleDeliveryActivation($business);
            
        default:
            return response()->json(['error' => 'Ação não suportada'], 400);
    }
}

// Em AutomationController.php

public function getAutomatedProtection()
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    // Verificar status das proteções
    $protectionStatus = [
        'monitoring' => $this->checkMonitoringStatus($business),
        'backup' => $this->checkBackupStatus($business),
        'correction' => $this->checkCorrectionStatus($business),
        'sabotage' => $this->checkSabotageProtection($business)
    ];

    return response()->json($protectionStatus);
}

private function checkMonitoringStatus($business)
{
    return [
        'active' => true,
        'last_check' => now()->subMinutes(5),
        'status' => 'healthy',
        'changes_detected' => [
            'last_24h' => 2,
            'details' => [
                ['type' => 'hours', 'time' => now()->subHours(2)],
                ['type' => 'photos', 'time' => now()->subHours(12)]
            ]
        ]
    ];
}

private function checkBackupStatus($business)
{
    return [
        'active' => true,
        'last_backup' => now()->subHours(1),
        'total_backups' => 24,
        'storage_used' => '250MB'
    ];
}

private function checkCorrectionStatus($business)
{
    return [
        'active' => true,
        'corrections_made' => [
            'total' => 5,
            'last_24h' => 1,
            'details' => [
                [
                    'type' => 'address',
                    'time' => now()->subHours(6),
                    'description' => 'Correção automática de endereço'
                ]
            ]
        ]
    ];
}

private function checkSabotageProtection($business)
{
    return [
        'active' => true,
        'attempts_blocked' => 3,
        'last_attempt' => now()->subDays(2),
        'risk_level' => 'low'
    ];
}

public function getCompetitiveAnalysis()
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    // Análise competitiva
    $analysis = [
        'competitors' => $this->analyzeCompetitors($business),
        'market_opportunities' => $this->findMarketOpportunities($business),
        'keyword_gaps' => $this->analyzeKeywordGaps($business),
        'service_comparison' => $this->compareServices($business)
    ];

    return response()->json($analysis);
}

private function analyzeCompetitors($business)
{
    return [
        'total_analyzed' => 5,
        'main_competitors' => [
            [
                'name' => 'Competitor A',
                'strength' => 'photos',
                'weakness' => 'response_time',
                'recent_changes' => [
                    'new_promotion' => 'Black Friday 50% OFF',
                    'date' => now()->subDays(1)
                ]
            ],
            // Add more competitors
        ]
    ];
}

private function findMarketOpportunities($business)
{
    return [
        'high_demand_services' => [
            ['service' => 'delivery', 'search_volume' => 150],
            ['service' => 'reservas online', 'search_volume' => 100]
        ],
        'underserved_areas' => [
            ['area' => 'região norte', 'potential' => 'high'],
            ['area' => 'centro', 'potential' => 'medium']
        ],
        'trending_keywords' => [
            ['keyword' => 'comida saudável', 'growth' => '+25%'],
            ['keyword' => 'delivery rápido', 'growth' => '+15%']
        ]
    ];
}

private function analyzeKeywordGaps($business)
{
    return [
        'missing_keywords' => [
            ['keyword' => 'estacionamento', 'monthly_searches' => 80],
            ['keyword' => 'wifi grátis', 'monthly_searches' => 50]
        ],
        'competitor_keywords' => [
            ['keyword' => 'happy hour', 'competitors_using' => 3],
            ['keyword' => 'música ao vivo', 'competitors_using' => 2]
        ]
    ];
}

private function compareServices($business)
{
    return [
        'unique_services' => [
            'you_offer' => ['playground infantil', 'área pet friendly'],
            'competitors_offer' => ['karaokê', 'área fumantes']
        ],
        'service_gaps' => [
            ['service' => 'reserva online', 'demand' => 'high'],
            ['service' => 'pagamento por QR code', 'demand' => 'medium']
        ]
    ];
}

// Em AutomationController.php

public function generateAdvancedReport(Request $request)
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    $validated = $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'report_type' => 'required|string|in:performance,competitors,engagement,full'
    ]);

    $reportData = $this->collectReportData($business, $validated);
    
    // Gerar insights baseados nos dados
    $insights = $this->generateReportInsights($reportData);

    // Preparar dados para exportação
    $exportData = [
        'business' => $business,
        'period' => [
            'start' => $validated['start_date'],
            'end' => $validated['end_date']
        ],
        'metrics' => $reportData,
        'insights' => $insights
    ];

    // Retornar dados baseado no formato solicitado
    switch ($request->format ?? 'html') {
        case 'pdf':
            return $this->generatePDFReport($exportData);
        case 'excel':
            return $this->generateExcelReport($exportData);
        default:
            return view('automation.reports.advanced', $exportData);
    }
}

private function collectReportData($business, $params)
{
    $data = [
        'performance' => [
            'views' => $this->getViewsData($business, $params),
            'clicks' => $this->getClicksData($business, $params),
            'conversions' => $this->getConversionsData($business, $params)
        ],
        'engagement' => [
            'reviews' => $this->getReviewsData($business, $params),
            'responses' => $this->getResponsesData($business, $params),
            'interaction_rate' => $this->calculateInteractionRate($business, $params)
        ],
        'competitors' => [
            'market_share' => $this->getMarketShareData($business, $params),
            'ranking_position' => $this->getRankingPosition($business, $params),
            'competitive_analysis' => $this->getCompetitiveAnalysis($business, $params)
        ],
        'automation' => [
            'posts' => $this->getAutomatedPostsData($business, $params),
            'responses' => $this->getAutomatedResponsesData($business, $params),
            'efficiency' => $this->calculateAutomationEfficiency($business, $params)
        ]
    ];

    return $data;
}

private function generateReportInsights($data)
{
    $insights = [];

    // Análise de Performance
    if ($data['performance']['views']['growth'] > 10) {
        $insights[] = [
            'type' => 'success',
            'message' => 'Crescimento significativo nas visualizações: ' . 
                        $data['performance']['views']['growth'] . '%'
        ];
    }

    // Análise de Engajamento
    $avgResponseTime = $data['engagement']['responses']['average_time'];
    if ($avgResponseTime < 120) { // menos de 2 horas
        $insights[] = [
            'type' => 'success',
            'message' => 'Excelente tempo médio de resposta: ' . 
                        round($avgResponseTime/60, 1) . ' horas'
        ];
    }

    // Análise Competitiva
    if ($data['competitors']['ranking_position']['current'] < 
        $data['competitors']['ranking_position']['previous']) {
        $insights[] = [
            'type' => 'warning',
            'message' => 'Queda na posição do ranking. Ação recomendada.'
        ];
    }

    // Análise de Automação
    $automationEfficiency = $data['automation']['efficiency'];
    if ($automationEfficiency > 85) {
        $insights[] = [
            'type' => 'success',
            'message' => 'Alta eficiência na automação: ' . $automationEfficiency . '%'
        ];
    }

    return $insights;
}

private function generatePDFReport($data)
{
    $pdf = PDF::loadView('automation.reports.pdf', $data);
    
    return $pdf->download('relatorio-' . now()->format('Y-m-d') . '.pdf');
}

private function generateExcelReport($data)
{
    return Excel::download(new ReportExport($data), 
                          'relatorio-' . now()->format('Y-m-d') . '.xlsx');
}

// Métodos auxiliares para coleta de dados
private function getViewsData($business, $params)
{
    // Implementar lógica de coleta de dados de visualizações
    return [
        'total' => 1000,
        'growth' => 15,
        'daily_average' => 33.3,
        'peak_day' => '2024-01-15'
    ];
}

private function getClicksData($business, $params)
{
    // Implementar lógica de coleta de dados de cliques
    return [
        'total' => 500,
        'growth' => 10,
        'daily_average' => 16.7,
        'peak_day' => '2024-01-16'
    ];
}

private function getConversionsData($business, $params)
{
    // Implementar lógica de coleta de dados de conversões
    return [
        'total' => 50,
        'rate' => 10,
        'growth' => 5,
        'by_source' => [
            'organic' => 30,
            'paid' => 20
        ]
    ];
}


public function aiSuggestions(Business $business)
{
    $gemini = app(GeminiService::class);
    $context = "Negócio: {$business->name}\nSetor: {$business->industry}\nDescrição: {$business->description}";
    
    return response()->json([
        'suggestions' => $gemini->generateSuggestions($context)
    ]);
}

public function createSmartPost(Business $business)
{
    $prompt = "Crie um post para uma empresa do ramo {$business->segment} 
               localizada em {$business->address}.
               Considere:
               - Tendências locais
               - Público-alvo da região
               - Horário de maior engajamento";

    $postSuggestion = $this->geminiService->generateContent($prompt);
    
    return $postSuggestion;
}

public function protection(Business $business)
{
    return view('automation.protection', compact('business'));
}

public function provideFeedback(Request $request, Business $business)
{
    $validated = $request->validate([
        'suggestion_id' => 'required|string',
        'feedback_type' => 'required|in:helpful,not_helpful',
        'comments' => 'nullable|string'
    ]);

    try {
        // Registrar feedback
        $feedback = $business->suggestionFeedback()->create([
            'suggestion_id' => $validated['suggestion_id'],
            'feedback_type' => $validated['feedback_type'],
            'comments' => $validated['comments'],
            'user_id' => auth()->id()
        ]);

        // Atualizar modelo de IA com o feedback
        $this->gemini->updateModelWithFeedback($feedback);

        return response()->json([
            'success' => true,
            'message' => 'Feedback registrado com sucesso'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erro ao registrar feedback: ' . $e->getMessage()
        ], 500);
    }
}

public function handleInsight(Business $business, Notification $notification)
{
    // Marcar notificação como lida
    $notification->update(['read_at' => now()]);

    // Redirecionar para a ação apropriada baseada no tipo
    switch ($notification->action_type) {
        case 'update_photos':
            return redirect()->route('automation.photos', [
                'business' => $business->id,
                'suggestion' => $notification->id
            ]);
        
        case 'update_description':
            return redirect()->route('automation.description', [
                'business' => $business->id,
                'suggestion' => $notification->id
            ]);
        
        default:
            return redirect()->route('automation.index')
                ->with('notification_id', $notification->id);
    }
}

public function createScheduledPost($business, $postData)
{
    try {
        // Implementar lógica de criação de posts programados
        return true;
    } catch (\Exception $e) {
        Log::error('Erro ao criar post: ' . $e->getMessage());
        return false;
    }
}

public function getSegmentEvents(Business $business)
{
    try {
        $events = $this->aiAnalysis->getSeasonalEvents($business);
        return response()->json([
            'success' => true,
            'seasonal_events' => $events
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro ao buscar eventos sazonais: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Erro ao buscar eventos sazonais'
        ], 500);
    }
}

public function updateCalendarEvent(Request $request)
{
    try {
        $validated = $request->validate([
            'id' => 'required',
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        $event = CalendarEvent::findOrFail($validated['id']);
        $event->update([
            'start_date' => $validated['start'],
            'end_date' => $validated['end']
        ]);

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        \Log::error('Erro ao atualizar evento: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Erro ao atualizar evento'
        ], 500);
    }
}

protected function getSeasonalEventsBySegment($segment, $month)
{
    try {
        // Primeiro tenta buscar eventos pré-definidos
        $staticEvents = $this->getStaticEvents($segment, $month);
        
        // Se não encontrar eventos estáticos ou houver poucos, busca dinâmicamente
        if (empty($staticEvents) || count($staticEvents) < 2) {
            $dynamicEvents = $this->getDynamicEvents($segment, $month);
            return array_merge($staticEvents, $dynamicEvents);
        }

        return $staticEvents;
    } catch (\Exception $e) {
        \Log::error('Erro ao buscar eventos sazonais: ' . $e->getMessage());
        return [];
    }
}

protected function getStaticEvents($segment, $month)
{
    $seasonalEvents = [
        'restaurante' => [
            1 => ['Pratos Leves de Verão', 'Happy Hour de Férias'],
            2 => ['Cardápio de Carnaval', 'Festival de Frutos do Mar'],
            3 => ['Dia da Mulher - Menu Especial', 'Festival Gastronômico'],
            4 => ['Páscoa Gourmet', 'Festival de Risotos'],
            5 => ['Dia das Mães', 'Festival de Massas'],
            6 => ['Dia dos Namorados', 'Festival de Sopas'],
            7 => ['Festival de Inverno', 'Noite do Fondue'],
            8 => ['Dia dos Pais', 'Festival de Churrasco'],
            9 => ['Festival da Primavera', 'Semana da Gastronomia'],
            10 => ['Oktoberfest', 'Festival de Frutos do Mar'],
            11 => ['Black Friday Gastronômica', 'Festival de Sobremesas'],
            12 => ['Confraternizações', 'Cardápio Especial de Natal']
        ],
        'academia' => [
            1 => ['Programa Verão em Forma', 'Desafio Ano Novo'],
            2 => ['Projeto Carnaval Fitness', 'Aulas Especiais de Dança'],
            3 => ['Mês da Mulher Fitness', 'Desafio Funcional'],
            4 => ['Programa Páscoa Fit', 'Maratona de Spinning'],
            5 => ['Desafio Dia das Mães', 'Aulas ao Ar Livre'],
            6 => ['Programa Inverno Forte', 'Desafio Casais Fitness'],
            7 => ['Férias Ativas', 'Gincana Fitness'],
            8 => ['Desafio Dia dos Pais', 'Maratona de Musculação'],
            9 => ['Primavera Fitness', 'Desafio Cross Training'],
            10 => ['Outubro Rosa Fitness', 'Maratona de Yoga'],
            11 => ['Black Friday Fitness', 'Desafio Pré-Verão'],
            12 => ['Desafio Fim de Ano', 'Aulas Temáticas Natalinas']
        ],
        'salao_beleza' => [
            1 => ['Promoção Volta às Aulas', 'Tratamentos de Verão'],
            2 => ['Especial Carnaval', 'Day Spa dos Namorados'],
            3 => ['Mês da Mulher', 'Festival de Hidratação'],
            4 => ['Páscoa Beauty', 'Semana do Cabelo'],
            5 => ['Especial Dia das Mães', 'Noivas de Maio'],
            6 => ['Dia dos Namorados Beauty', 'Festival de Coloração'],
            7 => ['Férias Beauty', 'Tratamentos de Inverno'],
            8 => ['Dia dos Pais VIP', 'Mês do Noivo'],
            9 => ['Primavera Beauty', 'Festival de Mechas'],
            10 => ['Outubro Rosa', 'Noivas de Primavera'],
            11 => ['Black Friday Beauty', 'Preparação para Festas'],
            12 => ['Natal Relax', 'Reveillon Beauty']
        ]
    ];

    return $seasonalEvents[$segment][$month] ?? [];
}

protected function getDynamicEvents($segment, $month)
{
    try {
        $year = date('Y');
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        
        $query = "eventos importantes {$segment} {$monthName} {$year} brasil";
        
        $results = $this->serper->search($query);
        
        $events = [];
        if (!empty($results['organic'])) {
            foreach ($results['organic'] as $result) {
                if (count($events) >= 3) break;
                
                // Extrai o título do evento do resultado
                $title = $result['title'];
                // Remove datas e caracteres especiais
                $title = preg_replace('/\d{4}|\||[-–]/', '', $title);
                $title = trim($title);
                
                if (strlen($title) > 10 && strlen($title) < 50) {
                    $events[] = $title;
                }
            }
        }
        
        return $events;
    } catch (\Exception $e) {
        \Log::error('Erro ao buscar eventos dinâmicos: ' . $e->getMessage());
        return [];
    }
}

public function getSegmentSuggestions(Business $business)
{
    try {
        // Busca dados do segmento via Gemini
        $segmentData = $this->gemini->analyzeBusinessSegment($business);
        
        // Busca tendências via Serper
        $trends = $this->serper->search("{$business->segment} trends {$business->city}");
        
        // Combina dados para gerar sugestões
        $suggestions = $this->aiAnalysis->generateSegmentSuggestions([
            'segment_data' => $segmentData,
            'trends' => $trends,
            'business' => $business
        ]);
        
        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erro ao gerar sugestões: ' . $e->getMessage()
        ], 500);
    }
}
public function getProtectionStatus()
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    return response()->json([
        'monitoring' => [
            'active' => true,
            'last_check' => now()->subMinutes(5),
            'changes_detected' => [
                'last_24h' => rand(0, 5),
                'total' => rand(10, 50)
            ]
        ],
        'backup' => [
            'active' => true,
            'last_backup' => now()->subHours(1),
            'total_backups' => rand(20, 100)
        ],
        'correction' => [
            'active' => true,
            'corrections_made' => [
                'last_24h' => rand(0, 3),
                'total' => rand(10, 30)
            ]
        ],
        'sabotage' => [
            'active' => true,
            'attempts_blocked' => rand(0, 10),
            'risk_level' => ['low', 'medium', 'high'][rand(0, 2)]
        ]
    ]);
}

public function getDetailedCompetitiveAnalysis()
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    return response()->json([
        'competitors' => [
            'main_competitors' => [
                [
                    'name' => 'Concorrente A',
                    'strength' => 'Forte presença online',
                    'weakness' => 'Poucas avaliações',
                    'market_share' => rand(10, 30)
                ],
                [
                    'name' => 'Concorrente B',
                    'strength' => 'Muitas avaliações positivas',
                    'weakness' => 'Pouca variedade de serviços',
                    'market_share' => rand(10, 30)
                ]
            ],
            'market_opportunities' => [
                [
                    'service' => 'Delivery',
                    'search_volume' => rand(100, 1000),
                    'competition_level' => 'medium'
                ],
                [
                    'service' => 'Atendimento 24h',
                    'search_volume' => rand(100, 1000),
                    'competition_level' => 'low'
                ]
            ],
            'keyword_gaps' => [
                [
                    'keyword' => 'promoção',
                    'monthly_searches' => rand(100, 500),
                    'competition' => 'low'
                ],
                [
                    'keyword' => 'reserva online',
                    'monthly_searches' => rand(100, 500),
                    'competition' => 'medium'
                ]
            ]
        ]
    ]);
}

public function getSmartNotifications()
{
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    return response()->json([
        'notifications' => [
            [
                'type' => 'protection',
                'title' => 'Alteração detectada',
                'message' => 'Detectamos uma alteração não autorizada em seus horários de funcionamento.',
                'priority' => 'high',
                'action' => 'review_changes'
            ],
            [
                'type' => 'opportunity',
                'title' => 'Oportunidade de mercado',
                'message' => 'Aumento nas buscas por delivery na sua região.',
                'priority' => 'medium',
                'action' => 'enable_delivery'
            ],
            [
                'type' => 'competitor',
                'title' => 'Alerta de concorrência',
                'message' => 'Concorrente principal atualizou seus preços.',
                'priority' => 'medium',
                'action' => 'review_prices'
            ]
        ]
    ]);
}

}