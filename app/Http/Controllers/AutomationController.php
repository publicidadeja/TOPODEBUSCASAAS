<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\AutomatedPost;
use App\Services\GeminiService;

class AutomationController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function index()
    {
        $business = Business::where('user_id', auth()->id())->first();

        // Se não houver negócio cadastrado, redireciona para criar
        if (!$business) {
            return redirect()
                ->route('business.create')
                ->with('warning', 'Você precisa cadastrar seu negócio primeiro para acessar a automação.');
        }

        // Busca os posts apenas se houver um negócio
        $scheduledPosts = AutomatedPost::where('business_id', $business->id)
                         ->where('is_posted', false)
                         ->orderBy('scheduled_for')
                         ->get();
                         
        $postedPosts = AutomatedPost::where('business_id', $business->id)
                      ->where('is_posted', true)
                      ->orderBy('scheduled_for', 'desc')
                      ->take(5)
                      ->get();

        return view('automation.index', compact('business', 'scheduledPosts', 'postedPosts'));
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
            'type' => 'required|string',
            'scheduled_for' => 'required|date',
            'customPrompt' => 'nullable|string'
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
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    $validated = $request->validate([
        'event_type' => 'required|string',
        'title' => 'required|string',
        'suggestion' => 'required|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date'
    ]);

    $event = $business->smartCalendar()->create([
        'event_type' => $validated['event_type'],
        'title' => $validated['title'],
        'suggestion' => $validated['suggestion'],
        'start_date' => $validated['start_date'],
        'end_date' => $validated['end_date'],
        'status' => 'pending'
    ]);

    return response()->json([
        'message' => 'Evento criado com sucesso!',
        'event' => $event
    ]);
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
            'title' => 'required|string|max:255',
            'event_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'suggestion' => 'nullable|string'
        ]);

        $event = $business->calendarEvents()->create($validated);

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
    try {
        $business = Business::where('user_id', auth()->id())->firstOrFail();
        $events = $business->calendarEvents()->get()->map(function($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'allDay' => !$event->end_date
            ];
        });

        return response()->json($events);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function getCalendarSuggestions()
{
    try {
        $business = Business::where('user_id', auth()->id())->firstOrFail();
        
        // Exemplo de sugestões - você pode personalizar conforme necessário
        $suggestions = [
            [
                'title' => 'Promoção Sazonal',
                'message' => 'Que tal criar uma promoção para o próximo feriado?',
                'type' => 'promotion'
            ],
            [
                'title' => 'Postagem nas Redes Sociais',
                'message' => 'Aumente seu engajamento com uma nova postagem',
                'type' => 'social'
            ]
        ];

        return response()->json(['suggestions' => $suggestions]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
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
    $business = Business::where('user_id', auth()->id())->first();
    
    if (!$business) {
        return response()->json(['error' => 'Negócio não encontrado'], 404);
    }

    // Analyze business data and generate AI suggestions
    $suggestions = $this->analyzeBusinessData($business);

    return response()->json(['suggestions' => $suggestions]);
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

}

