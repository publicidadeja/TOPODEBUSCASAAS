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

    // Exemplo de sugestões para restaurantes
    if (strtolower($segment) === 'restaurante') {
        switch ($currentMonth) {
            case 6: // Junho
                $suggestions[] = [
                    'type' => 'event',
                    'title' => 'Dia dos Namorados',
                    'message' => 'Que tal criar um menu especial para o Dia dos Namorados?'
                ];
                break;
            case 11: // Novembro
                $suggestions[] = [
                    'type' => 'promotion',
                    'title' => 'Black Friday',
                    'message' => 'Prepare suas promoções para a Black Friday!'
                ];
                break;
            case 12: // Dezembro
                $suggestions[] = [
                    'type' => 'hours',
                    'title' => 'Fim de Ano',
                    'message' => 'Configure os horários especiais de fim de ano'
                ];
                break;
        }
    }

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

}

