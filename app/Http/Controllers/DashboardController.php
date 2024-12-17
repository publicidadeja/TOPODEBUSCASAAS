<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessAnalytics;
use App\Services\GoogleBusinessService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $googleService;

    public function __construct(GoogleBusinessService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function index(Request $request)
{
    $user = auth()->user();
    $businesses = $user->businesses;
    
    // Se não houver negócio selecionado, pega o primeiro
    $selectedBusiness = null;
    if ($businesses->isNotEmpty()) {
        $selectedBusiness = $businesses->first();
    }

    // Se houver um businessId na requisição, use-o
    if ($request->has('businessId')) {
        $selectedBusiness = $businesses->find($request->businessId);
    }

    // Se não houver negócio, redirecione para criar um
    if (!$selectedBusiness) {
        return redirect()->route('business.create')
            ->with('warning', 'Por favor, cadastre um negócio primeiro.');
    }

    try {
        // Tentar obter dados do Google
        $googleInsights = $this->googleService->getBusinessInsights($selectedBusiness->id);
        
        // Nova implementação para buscar competidores
        $competitors = $this->googleService->getNearbyCompetitors([
            'location' => [
                'lat' => $selectedBusiness->latitude,
                'lng' => $selectedBusiness->longitude
            ],
            'radius' => 5000, // 5km radius
            'type' => $selectedBusiness->business_type ?? 'establishment',
            'keyword' => $selectedBusiness->keywords ?? $selectedBusiness->name,
            'limit' => 5
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao obter dados do Google: ' . $e->getMessage());
        $googleInsights = null;
        $competitors = [];
        session()->flash('google_error', 'Não foi possível obter dados do Google My Business. Verifique sua conexão.');
    }

    // Buscar analytics dos últimos 30 dias
    $endDate = Carbon::now();
    $startDate = Carbon::now()->subDays(30);

    $analytics = BusinessAnalytics::where('business_id', $selectedBusiness->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date')
        ->get();

    // Calcular totais
    $totalViews = $googleInsights ? $googleInsights['views']['total'] : $analytics->sum('views');
    $totalClicks = $googleInsights ? $googleInsights['clicks']['total'] : $analytics->sum('clicks');
    $totalCalls = $googleInsights ? $googleInsights['calls']['total'] : $analytics->sum('calls');

    // Calcular taxa de conversão
    $conversionRate = $totalViews > 0 
        ? round((($totalClicks + $totalCalls) / $totalViews) * 100, 1)
        : 0;

    // Preparar dados para o dashboard
    $analyticsData = [
        'views' => $totalViews,
        'clicks' => $totalClicks,
        'calls' => $totalCalls,
        'conversion_rate' => $conversionRate,
        'dates' => $analytics->pluck('date')->map(fn($date) => $date->format('d/m')),
        'daily_views' => $analytics->pluck('views'),
        'daily_clicks' => $analytics->pluck('clicks'),
        'daily_calls' => $analytics->pluck('calls'),
        'devices' => $analytics->last()?->devices ?? [
            'desktop' => 0,
            'mobile' => 0,
            'tablet' => 0
        ],
        'top_locations' => $this->getTopLocations($analytics),
        'trends' => $this->calculateTrends($analytics)
    ];

    // Calcular variações percentuais
    $analyticsData['trends'] = [
        'views' => $googleInsights ? ($googleInsights['views']['trend'] ?? 0) : $analyticsData['trends']['views'],
        'clicks' => $googleInsights ? ($googleInsights['clicks']['trend'] ?? 0) : $analyticsData['trends']['clicks'],
        'calls' => $googleInsights ? ($googleInsights['calls']['trend'] ?? 0) : $analyticsData['trends']['calls'],
        'conversion' => $analyticsData['trends']['conversion']
    ];

    // Gerar insights e sugestões
    $suggestions = $this->generateSuggestions($analyticsData, $selectedBusiness, $competitors);

    return view('dashboard', [
        'businesses' => $businesses,
        'selectedBusiness' => $selectedBusiness,
        'analytics' => $analyticsData,
        'suggestions' => $suggestions,
        'competitors' => $competitors
    ]);
}

    private function getTopLocations($analytics)
    {
        $locations = [];
        foreach ($analytics as $analytic) {
            if (!empty($analytic->user_locations)) {
                foreach ($analytic->user_locations as $location => $count) {
                    if (!isset($locations[$location])) {
                        $locations[$location] = 0;
                    }
                    $locations[$location] += $count;
                }
            }
        }
        arsort($locations);
        $total = array_sum($locations);
        
        return collect(array_slice($locations, 0, 5, true))
            ->map(fn($count) => round(($count / $total) * 100, 1))
            ->toArray();
    }

    private function calculateTrends($analytics)
    {
        if ($analytics->count() < 2) {
            return [
                'views' => 0,
                'clicks' => 0,
                'calls' => 0,
                'conversion' => 0
            ];
        }

        $midPoint = floor($analytics->count() / 2);
        $firstHalf = $analytics->take($midPoint);
        $secondHalf = $analytics->skip($midPoint);

        return [
            'views' => $this->calculateTrendPercentage(
                $firstHalf->avg('views'),
                $secondHalf->avg('views')
            ),
            'clicks' => $this->calculateTrendPercentage(
                $firstHalf->avg('clicks'),
                $secondHalf->avg('clicks')
            ),
            'calls' => $this->calculateTrendPercentage(
                $firstHalf->avg('calls'),
                $secondHalf->avg('calls')
            ),
            'conversion' => $this->calculateTrendPercentage(
                $firstHalf->avg('clicks') / max($firstHalf->avg('views'), 1) * 100,
                $secondHalf->avg('clicks') / max($secondHalf->avg('views'), 1) * 100
            )
        ];
    }

    private function calculateTrendPercentage($oldValue, $newValue)
    {
        if ($oldValue == 0) return 0;
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }

    private function generateSuggestions($analyticsData, $selectedBusiness, $competitors = [])
    {
        $suggestions = [];

        // Verificar tendências negativas
        if ($analyticsData['trends']['views'] < 0) {
            $suggestions[] = [
                'type' => 'warning',
                'message' => 'As visualizações diminuíram em comparação com o período anterior. Considere revisar suas palavras-chave e conteúdo.',
                'action' => 'Revisar SEO',
                'action_url' => route('business.edit', ['business' => $selectedBusiness->id])
            ];
        }

        // Análise de concorrentes
        if (!empty($competitors)) {
            foreach ($competitors as $competitor) {
                if (isset($competitor['insights']['views']) && 
                    $competitor['insights']['views'] > $analyticsData['views']) {
                    $suggestions[] = [
                        'type' => 'info',
                        'message' => "O concorrente {$competitor['name']} tem mais visualizações. Considere analisar suas estratégias de visibilidade.",
                        'action' => 'Ver Análise Competitiva',
                        'action_url' => route('analytics.competitive', ['business' => $selectedBusiness->id])
                    ];
                    break; // Limita a uma sugestão de concorrente
                }
            }
        }

        // Verificar dispositivos móveis
        if (($analyticsData['devices']['mobile'] ?? 0) < 30) {
            $suggestions[] = [
                'type' => 'info',
                'message' => 'Seu site tem poucos acessos via dispositivos móveis. Certifique-se que está otimizado para smartphones.',
                'action' => 'Verificar Responsividade',
                'action_url' => $selectedBusiness->website
            ];
        }

        // Sugestões positivas
        if ($analyticsData['trends']['views'] > 20) {
            $suggestions[] = [
                'type' => 'success',
                'message' => 'Parabéns! Suas visualizações aumentaram significativamente.',
                'action' => 'Ver Detalhes',
                'action_url' => route('analytics.index', ['business' => $selectedBusiness->id])
            ];
        }

        return $suggestions;
    }

    private function getCompetitors($business)
{
    if (!$business->latitude || !$business->longitude) {
        \Log::warning('Business location not set for business ID: ' . $business->id);
        return [];
    }

    try {
        // Get competitors from Google Places API using the business location
        $competitors = $this->googleService->getNearbyCompetitors([
            'location' => [
                'lat' => $business->latitude,
                'lng' => $business->longitude
            ],
            'radius' => 5000, // 5km radius
            'type' => $business->business_type ?? 'establishment', // Use business type or default to 'establishment'
            'keyword' => $business->keywords ?? $business->name, // Use keywords or business name
            'limit' => 5 // Get top 5 competitors
        ]);

        return $competitors;
    } catch (\Exception $e) {
        \Log::error('Error fetching competitors: ' . $e->getMessage());
        return [];
    }
}
}