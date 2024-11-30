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
}