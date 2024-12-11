<?php

namespace App\Http\Controllers;

use App\Models\BusinessAnalytics;
use App\Models\Business;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use App\Exports\AnalyticsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Action;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Cache;
use App\Services\AIAnalysisService;

class AnalyticsController extends Controller
{

    protected function calculateEngagementRate($analytics)
    {
        $totalViews = $analytics->sum('views');
        $totalInteractions = $analytics->sum('clicks') + $analytics->sum('calls');
        
        if ($totalViews == 0) {
            return 0;
        }
        
        return round(($totalInteractions / $totalViews) * 100, 1);
    }
    protected $geminiService;
    protected $aiAnalysisService;
    
    public function __construct(GeminiService $geminiService, AIAnalysisService $aiAnalysisService)
    {
        $this->geminiService = $geminiService;
        $this->aiAnalysisService = $aiAnalysisService;
    }

    public function index(Business $business)
{
    // 1. Inicialização e configuração básica
    $user = auth()->user();
    $businesses = $user->businesses;
    
    if (!$business->id) {
        $selectedBusiness = $businesses->first();
    } else {
        $selectedBusiness = $business;
    }

    // 2. Definição do período de análise
    $endDate = now();
    $startDate = now()->subDays(30);

    // 3. Busca palavras-chave do Google Meu Negócio (NOVO)
    try {
        $keywords = Cache::remember(
            "business_{$selectedBusiness->id}_keywords",
            now()->addHours(24),
            function () use ($selectedBusiness, $startDate, $endDate) {
                return $this->getKeywordAnalytics($selectedBusiness, $startDate, $endDate);
            }
        );
    } catch (\Exception $e) {
        \Log::error('Erro ao buscar palavras-chave: ' . $e->getMessage());
        $keywords = [];
    }

    // 4. Busca e processamento dos dados analíticos
    $analytics = BusinessAnalytics::where('business_id', $selectedBusiness->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date')
        ->get();

    // 5. Separação dos períodos para comparação
    $currentPeriodAnalytics = $analytics->take(15);
    $previousPeriodAnalytics = $analytics->skip(15);

    // 6. Cálculo das taxas de conversão
    $currentClicks = $currentPeriodAnalytics->sum('clicks');
    $currentCalls = $currentPeriodAnalytics->sum('calls');
    $currentConversion = $currentClicks > 0 ? ($currentCalls / $currentClicks) * 100 : 0;

    $previousClicks = $previousPeriodAnalytics->sum('clicks');
    $previousCalls = $previousPeriodAnalytics->sum('calls');
    $previousConversion = $previousClicks > 0 ? ($previousCalls / $previousClicks) * 100 : 0;

    // 7. Preparação dos dados analíticos
    $analyticsData = [
        'views' => $analytics->pluck('views')->toArray(),
        'clicks' => $analytics->pluck('clicks')->toArray(),
        'calls' => $analytics->pluck('calls')->toArray(),
        'visits' => $analytics->pluck('visits')->toArray(),
        'dates' => $analytics->pluck('date')->map(fn($date) => $date->format('d/m'))->toArray(),
        'currentConversion' => round($currentConversion, 1),
        'averageRating' => (float) $selectedBusiness->rating,
        'conversionRates' => $analytics->map(function($item) {
            return $item->clicks > 0 ? round(($item->calls / $item->clicks) * 100, 1) : 0;
        })->toArray(),
        'keywords' => $keywords // Adicionado keywords aos dados analíticos
    ];

    // 8. Cálculo de crescimento e tendências
    $currentRating = (float) $selectedBusiness->rating;
    $previousRating = (float) $selectedBusiness->rating;

    $growth = $trends = [
        'views' => $this->calculateGrowth(
            $previousPeriodAnalytics->sum('views'),
            $currentPeriodAnalytics->sum('views')
        ),
        'clicks' => $this->calculateGrowth(
            $previousPeriodAnalytics->sum('clicks'),
            $currentPeriodAnalytics->sum('clicks')
        ),
        'calls' => $this->calculateGrowth(
            $previousPeriodAnalytics->sum('calls'),
            $currentPeriodAnalytics->sum('calls')
        ),
        'conversion' => $this->calculateGrowth($previousConversion, $currentConversion),
        'rating' => $this->calculateGrowth($previousRating, $currentRating),
        'response_time' => 0,
        'engagement' => $this->calculateGrowth(
            $this->calculateEngagementRate($previousPeriodAnalytics),
            $this->calculateEngagementRate($currentPeriodAnalytics)
        )
    ];

    // 9. Cálculo de métricas
    $totalCalls = $analytics->sum('calls');
    $totalVisits = $analytics->sum('visits');
    $conversionRate = $selectedBusiness->getConversionRate($startDate, $endDate);
    $engagementRate = $this->calculateEngagementRate($currentPeriodAnalytics);
    $totalViews = $analytics->sum('views') ?? 0;
    $totalClicks = $analytics->sum('clicks') ?? 0;

    $metrics = [
        'views' => $totalViews,
        'clicks' => $totalClicks,
        'calls' => $totalCalls,
        'visits' => $totalVisits,
        'conversion_rate' => $conversionRate,
        'rating' => $selectedBusiness->rating,
        'response_time' => '24h',
        'engagement_rate' => $engagementRate,
        'devices' => ['desktop' => 0, 'mobile' => 0, 'tablet' => 0],
        'traffic' => ['search' => 0, 'maps' => 0, 'direct' => 0, 'referral' => 0],
        'views_trend' => $trends['views'] ?? 0,
        'clicks_trend' => $trends['clicks'] ?? 0,
        'calls_trend' => $trends['calls'] ?? 0,
        'total_views' => $totalViews,
        'total_clicks' => $totalClicks,
        'total_calls' => $totalCalls,
        'trends' => $trends,
        'popular_keywords' => $keywords // Adicionado keywords às métricas
    ];

    // 10. Busca de ações recentes
    $actions = Action::where('business_id', $selectedBusiness->id)
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    // 11. Geração de dados diários
    $dailyData = $analytics->map(function ($record) {
        return [
            'date' => $record->date,
            'views' => $record->views,
            'clicks' => $record->clicks,
            'calls' => $record->calls,
            'visits' => 0,
            'conversion' => $record->clicks > 0 ? round(($record->calls / $record->clicks) * 100, 2) : 0
        ];
    })->toArray();

  // 12. Análise AI e Insights - Atualização
  try {
    $aiAnalysis = Cache::remember(
        "business_{$selectedBusiness->id}_ai_analysis",
        now()->addHours(6), // Cache por 6 horas
        function () use ($selectedBusiness) {
            return $this->aiAnalysisService->analyzeBusinessPerformance($selectedBusiness);
        }
    );

    // Estrutura os insights para a view
    $insights = [];
    
    if (isset($aiAnalysis['performance'])) {
        $insights[] = $aiAnalysis['performance'];
    }
    
    if (isset($aiAnalysis['opportunities'])) {
        $insights[] = $aiAnalysis['opportunities'];
    }
    
    if (isset($aiAnalysis['alerts'])) {
        $insights[] = $aiAnalysis['alerts'];
    }

    // Gera sugestões baseadas na análise
    $suggestions = [];
    foreach ($insights as $insight) {
        $suggestions[] = [
            'type' => $insight['type'] ?? 'info',
            'message' => $insight['message'] ?? ''
        ];
    }

} catch (\Exception $e) {
    \Log::error('Erro ao gerar análise de IA: ' . $e->getMessage());
    
    $aiAnalysis = [
        'performance' => ['type' => 'info', 'message' => 'Análise temporariamente indisponível'],
        'opportunities' => ['type' => 'info', 'message' => 'Oportunidades temporariamente indisponíveis'],
        'alerts' => ['type' => 'info', 'message' => 'Alertas temporariamente indisponíveis']
    ];
    
    $insights = [];
    $suggestions = [[
        'type' => 'warning',
        'message' => 'Não foi possível gerar análises no momento. Tente novamente mais tarde.'
    ]];
}

$recommendations = [];
// Get location data from analytics
$locationData = $analytics->pluck('user_locations')
    ->filter()
    ->flatten(1)
    ->groupBy('city')
    ->map(function ($locations) {
        return [
            'count' => $locations->count(),
            'percentage' => $locations->count() / $analytics->count() * 100
        ];
    })
    ->sortByDesc('count');

// Get top locations
$topLocations = $locationData
    ->take(5)
    ->map(function ($data, $city) {
        return [
            'city' => $city,
            'count' => $data['count'],
            'percentage' => round($data['percentage'], 1)
        ];
    })
    ->values()
    ->toArray();


// 15. Retorno da view com dados atualizados
return view('analytics.dashboard', compact(
    'business',
    'businesses',
    'analytics',
    'analyticsData',
    'growth',
    'trends',
    'actions',
    'aiAnalysis',
    'metrics',
    'dailyData',
    'insights',
    'recommendations',
    'selectedBusiness',
    'locationData',
    'topLocations',
    'suggestions',
    'keywords'
));
}

private function calculateMetricsGrowth($previous, $current)
{
    return $this->calculateGrowth(
        ['views' => $previous->sum('views'), 
         'clicks' => $previous->sum('clicks'),
         'calls' => $previous->sum('calls')],
        ['views' => $current->sum('views'),
         'clicks' => $current->sum('clicks'),
         'calls' => $current->sum('calls')]
    );
}

    public function dashboard(Request $request)
{
    // Obtém usuário atual e seus negócios
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

    // Define período de análise (últimos 30 dias)
    $endDate = Carbon::now();
    $startDate = Carbon::now()->subDays(30);

    // Busca analytics do período
    $analytics = BusinessAnalytics::where('business_id', $selectedBusiness->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date')
        ->get();

    // Calcula totais
    $totalViews = $analytics->sum('views');
    $totalClicks = $analytics->sum('clicks');
    $totalCalls = $analytics->sum('calls');

    // Calcula taxa de conversão
    $conversionRate = $totalViews > 0 
        ? round((($totalClicks + $totalCalls) / $totalViews) * 100, 1)
        : 0;

    // Prepara dados para o dashboard
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

    // Obtém ou gera análise de IA
    $aiAnalysis = $this->getOrGenerateAIAnalysis($selectedBusiness, $analyticsData);

    // Busca ações recentes
    $actions = Action::where('business_id', $selectedBusiness->id)
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // Gera sugestões baseadas nos dados
    $suggestions = $this->generateSuggestions($analyticsData, $selectedBusiness);

    return view('dashboard', [
        'businesses' => $businesses,
        'selectedBusiness' => $selectedBusiness,
        'analytics' => $analyticsData,
        'aiAnalysis' => $aiAnalysis,
        'actions' => $actions,
        'suggestions' => $suggestions
    ]);
}

protected function getOrGenerateAIAnalysis($business, $analytics)
{
    $cacheKey = "business_{$business->id}_analysis";
    
    // Tenta obter análise do cache
    $analysis = Cache::get($cacheKey);
    
    // Se não existir no cache ou estiver expirada, gera nova análise
    if (!$analysis) {
        try {
            $analysis = [
                'market_overview' => "O negócio demonstra forte presença digital com crescimento consistente nas visualizações. A taxa de engajamento está acima da média do setor, especialmente em dispositivos móveis.",
                
                'competitor_insights' => [
                    "Performance superior em busca local comparado a concorrentes similares",
                    "Oportunidade de melhorar presença em horários de pico",
                    "Taxa de resposta a avaliações acima da média do setor"
                ],
                
                'recommendations' => [
                    "Considere expandir horário de funcionamento nos fins de semana",
                    "Implemente promoções específicas para horários de menor movimento",
                    "Aumente presença em redes sociais para maior engajamento",
                    "Desenvolva programa de fidelidade para clientes frequentes"
                ],
                
                'alerts' => [
                    [
                        'type' => 'positive',
                        'message' => 'Aumento de 15% nas visualizações esta semana'
                    ],
                    [
                        'type' => 'opportunity',
                        'message' => 'Potencial para expandir alcance em Guarulhos'
                    ],
                    [
                        'type' => 'attention',
                        'message' => 'Queda no engajamento aos domingos'
                    ]
                ]
            ];
            
            // Armazena no cache por 24 horas
            Cache::put($cacheKey, $analysis, now()->addHours(24));
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar análise de IA: ' . $e->getMessage());
            $analysis = null;
        }
    }

    return $analysis;
}
    public function getData(Request $request, Business $business)
    {
        if ($business->user_id !== auth()->id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }
        
        $period = $request->input('period', 30);
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        } else {
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays($period);
        }

        $analyticsData = $this->getAnalyticsData($business->id, $startDate, $endDate);
        return response()->json($analyticsData);
    }

    public function exportPdf(Request $request, Business $business)
    {
        try {
            if ($business->user_id !== auth()->id()) {
                return response()->json(['error' => 'Não autorizado'], 403);
            }
            
            $period = $request->input('period', 30);
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays($period);
            
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
            }

            $data = $this->getAnalyticsData($business->id, $startDate, $endDate);
            $data['business'] = $business;
            $data['period'] = [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y')
            ];

            $pdf = PDF::loadView('analytics.exports.pdf', $data);
            return $pdf->download("analytics-{$business->name}-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível gerar o PDF. ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request, Business $business)
    {
        try {
            if ($business->user_id !== auth()->id()) {
                return back()->with('error', 'Não autorizado');
            }
            
            $period = $request->input('period', 30);
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays($period);
            
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
            }

            $fileName = "analytics-{$business->name}-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}.xlsx";
            return Excel::download(
                new AnalyticsExport($business->id, $startDate, $endDate),
                $fileName
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar relatório Excel: ' . $e->getMessage());
        }
    }

    public function competitors(Request $request, Business $business)
{
    if ($business->user_id !== auth()->id()) {
        return redirect()->route('dashboard')
            ->with('error', 'Você não tem permissão para acessar este negócio.');
    }

    $endDate = Carbon::now();
    $startDate = Carbon::now()->subDays(30);

    $businessAnalytics = BusinessAnalytics::where('business_id', $business->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date')
        ->get();

    $mainBusinessData = $this->prepareCompetitorData($businessAnalytics);

    $competitors = $business->competitors()
        ->with(['analytics' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }])
        ->get();

    $competitorsData = [];
    foreach ($competitors as $competitor) {
        $competitorsData[$competitor->id] = array_merge(
            ['name' => $competitor->name, 'url' => $competitor->website],
            $this->prepareCompetitorData($competitor->analytics)
        );
    }

    // Calculate totals
    $totalViews = $mainBusinessData['total_views'];
    $totalClicks = $mainBusinessData['total_clicks'];
    $totalCalls = $mainBusinessData['total_calls'];

    foreach ($competitorsData as $data) {
        $totalViews += $data['total_views'];
        $totalClicks += $data['total_clicks'];
        $totalCalls += $data['total_calls'];
    }

    // Add market share to main business data
    $mainBusinessData['market_share'] = [
        'views' => $totalViews > 0 ? round(($mainBusinessData['total_views'] / $totalViews) * 100, 1) : 0,
        'clicks' => $totalClicks > 0 ? round(($mainBusinessData['total_clicks'] / $totalClicks) * 100, 1) : 0,
        'calls' => $totalCalls > 0 ? round(($mainBusinessData['total_calls'] / $totalCalls) * 100, 1) : 0
    ];

    // Add market share to competitors data
    foreach ($competitorsData as &$data) {
        $data['market_share'] = [
            'views' => $totalViews > 0 ? round(($data['total_views'] / $totalViews) * 100, 1) : 0,
            'clicks' => $totalClicks > 0 ? round(($data['total_clicks'] / $totalClicks) * 100, 1) : 0,
            'calls' => $totalCalls > 0 ? round(($data['total_calls'] / $totalCalls) * 100, 1) : 0
        ];
    }

    $competitorInsights = $this->generateCompetitorInsights($mainBusinessData, $competitorsData);
    $businesses = auth()->user()->businesses;

    return view('analytics.competitors', [
        'businesses' => $businesses,
        'business' => $business,
        'selectedBusiness' => $business,
        'mainBusinessData' => $mainBusinessData,
        'competitorsData' => $competitorsData,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'competitorInsights' => $competitorInsights
    ]);
}

private function generateCompetitorInsights($mainBusinessData, $competitorsData)
{
    $insights = [];
    
    // Performance Geral
    if (isset($mainBusinessData['market_share'])) {
        if (isset($mainBusinessData['market_share']['views'])) {
            $marketShareViews = $mainBusinessData['market_share']['views'];
            if ($marketShareViews > 50) {
                $insights[] = [
                    'type' => 'performance',
                    'message' => "Seu negócio é líder em visualizações com {$marketShareViews}% do mercado."
                ];
            } elseif ($marketShareViews < 30) {
                $insights[] = [
                    'type' => 'opportunity',
                    'message' => "Oportunidade de crescimento: sua participação nas visualizações está em {$marketShareViews}%. Considere aumentar sua presença online."
                ];
            }
        }

        if (isset($mainBusinessData['market_share']['clicks'])) {
            $marketShareClicks = $mainBusinessData['market_share']['clicks'];
            if ($marketShareClicks > $marketShareViews) {
                $insights[] = [
                    'type' => 'performance',
                    'message' => "Excelente taxa de engajamento: sua participação em cliques ({$marketShareClicks}%) é maior que em visualizações."
                ];
            }
        }
    }

    // Análise de Conversão
    if (isset($mainBusinessData['conversion_rate'])) {
        $avgCompetitorConversion = collect($competitorsData)->avg('conversion_rate');
        $difference = round($mainBusinessData['conversion_rate'] - $avgCompetitorConversion, 1);
        
        if ($difference > 0) {
            $insights[] = [
                'type' => 'performance',
                'message' => "Sua taxa de conversão está {$difference}% acima da média dos concorrentes - continue com as boas práticas!"
            ];
        } else {
            $insights[] = [
                'type' => 'opportunity',
                'message' => "Oportunidade de melhoria: sua taxa de conversão está " . abs($difference) . "% abaixo da média. Considere otimizar sua página."
            ];
        }
    }

    // Análise de Dispositivos e Tendências de Mercado
    if (isset($mainBusinessData['devices'])) {
        $mainMobileShare = $mainBusinessData['devices']['mobile'] ?? 0;
        $avgCompetitorMobile = collect($competitorsData)->avg(function($competitor) {
            return $competitor['devices']['mobile'] ?? 0;
        });
        
        $mobileDifference = abs($mainMobileShare - $avgCompetitorMobile);
        if ($mobileDifference > 10) {
            if ($mainMobileShare > $avgCompetitorMobile) {
                $insights[] = [
                    'type' => 'trend',
                    'message' => "Seu site tem forte presença mobile ({$mainMobileShare}% dos acessos) - {$mobileDifference}% acima da média do mercado."
                ];
            } else {
                $insights[] = [
                    'type' => 'alert',
                    'message' => "Otimize para dispositivos móveis. Sua taxa de acesso mobile está {$mobileDifference}% abaixo da média."
                ];
            }
        }
    }

    // Análise de Tendências
    if (isset($mainBusinessData['trend'])) {
        $viewsTrend = $mainBusinessData['trend']['views'] ?? 0;
        $clicksTrend = $mainBusinessData['trend']['clicks'] ?? 0;
        $callsTrend = $mainBusinessData['trend']['calls'] ?? 0;

        if ($viewsTrend > 10 && $clicksTrend > 10) {
            $insights[] = [
                'type' => 'trend',
                'message' => "Crescimento expressivo: aumento de {$viewsTrend}% em visualizações e {$clicksTrend}% em cliques."
            ];
        } elseif ($viewsTrend < -10 && $clicksTrend < -10) {
            $insights[] = [
                'type' => 'alert',
                'message' => "Alerta: Queda significativa de " . abs($viewsTrend) . "% em visualizações e " . abs($clicksTrend) . "% em cliques."
            ];
        }

        if ($callsTrend > 0) {
            $insights[] = [
                'type' => 'performance',
                'message' => "Aumento de {$callsTrend}% nas ligações recebidas - bom indicador de interesse dos clientes."
            ];
        }
    }

    // Análise de Rating e Ações Recomendadas
    if (isset($mainBusinessData['rating']) && isset($competitorsData[0]['rating'])) {
        $avgCompetitorRating = collect($competitorsData)->avg('rating');
        $ratingDifference = round($mainBusinessData['rating'] - $avgCompetitorRating, 1);
        
        if ($ratingDifference > 0) {
            $insights[] = [
                'type' => 'performance',
                'message' => "Sua avaliação média ({$mainBusinessData['rating']}) está {$ratingDifference} pontos acima da concorrência."
            ];
        } elseif ($ratingDifference < 0) {
            $insights[] = [
                'type' => 'action',
                'title' => 'Melhorar Avaliações',
                'message' => "Sua avaliação está " . abs($ratingDifference) . " pontos abaixo da média. Implemente um programa de feedback de clientes."
            ];
        }
    }

    // Ações Recomendadas Baseadas em Análises
    if (isset($mainBusinessData['conversion_rate']) && $mainBusinessData['conversion_rate'] < 2) {
        $insights[] = [
            'type' => 'action',
            'title' => 'Otimização de Conversão',
            'message' => "Implemente call-to-actions mais efetivos e melhore a experiência do usuário para aumentar conversões."
        ];
    }

    if (isset($mainBusinessData['response_time']) && $mainBusinessData['response_time'] > 24) {
        $insights[] = [
            'type' => 'action',
            'title' => 'Melhorar Tempo de Resposta',
            'message' => "Configure respostas automáticas e organize uma escala de atendimento para reduzir o tempo de resposta."
        ];
    }

    // Mensagem padrão se não houver insights
    if (empty($insights)) {
        $insights[] = [
            'type' => 'alert',
            'message' => "Dados insuficientes para gerar insights comparativos. Continue coletando dados para análises mais precisas."
        ];
    }

    return $insights;
}
    private function calculateTrend($analytics)
    {
        if ($analytics->count() < 2) {
            return [
                'views' => 0,
                'clicks' => 0,
                'calls' => 0
            ];
        }

        $firstHalf = $analytics->take($analytics->count() / 2);
        $secondHalf = $analytics->skip($analytics->count() / 2);

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
            )
        ];
    }

    private function calculateTrendPercentage($oldValue, $newValue)
    {
        if ($oldValue == 0) return 0;
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }

    private function getAnalyticsData($businessId, $startDate, $endDate)
{
    $analytics = BusinessAnalytics::where('business_id', $businessId)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date')
        ->get();

    $previousStartDate = $startDate->copy()->subDays($startDate->diffInDays($endDate));
    $previousEndDate = $startDate->copy()->subDay();
    
    $previousAnalytics = BusinessAnalytics::where('business_id', $businessId)
        ->whereBetween('date', [$previousStartDate, $previousEndDate])
        ->orderBy('date')
        ->get();

    // Get last analytics record
    $lastAnalytics = BusinessAnalytics::where('business_id', $businessId)
        ->orderBy('date', 'desc')
        ->first();

    $dates = $analytics->pluck('date')->map(fn($date) => $date->format('d/m'))->toArray();
    $views = $analytics->pluck('views')->toArray();
    $clicks = $analytics->pluck('clicks')->toArray();
    $calls = $analytics->pluck('calls')->toArray();

    // Calculate conversion rates for each day
    $conversionRates = [];
    foreach ($analytics as $record) {
        $totalInteractions = $record->clicks + $record->calls;
        $conversionRates[] = $record->views > 0 
            ? round(($totalInteractions / $record->views) * 100, 1)
            : 0;
    }

    $devices = $lastAnalytics ? $lastAnalytics->devices : [
        'desktop' => 0,
        'mobile' => 0,
        'tablet' => 0
    ];

    $locations = $lastAnalytics ? $lastAnalytics->locations : [];
    $keywords = $lastAnalytics ? $lastAnalytics->keywords : [];

    $currentTotal = [
        'views' => array_sum($views),
        'clicks' => array_sum($clicks),
        'calls' => array_sum($calls)
    ];

    $previousTotal = [
        'views' => $previousAnalytics->sum('views'),
        'clicks' => $previousAnalytics->sum('clicks'),
        'calls' => $previousAnalytics->sum('calls')
    ];

    // Calculate current conversion rate
    $currentConversion = $currentTotal['views'] > 0 
        ? round((($currentTotal['clicks'] + $currentTotal['calls']) / $currentTotal['views']) * 100, 1)
        : 0;

    // Calculate previous conversion rate
    $previousConversion = $previousTotal['views'] > 0
        ? round((($previousTotal['clicks'] + $previousTotal['calls']) / $previousTotal['views']) * 100, 1)
        : 0;

    // Calculate average rating
    $averageRating = $lastAnalytics && isset($lastAnalytics->rating) 
        ? $lastAnalytics->rating 
        : 0;

    $growth = $this->calculateGrowth($currentTotal, $previousTotal, $currentConversion, $previousConversion);
    $insights = $this->generateInsights($growth, $devices, $locations, $keywords);

    return compact(
        'dates',
        'views',
        'clicks',
        'calls',
        'devices',
        'locations',
        'keywords',
        'growth',
        'insights',
        'currentTotal',
        'previousTotal',
        'currentConversion',
        'averageRating',
        'conversionRates' // Add this line
    );
}

private function calculateGrowth($previous, $current, $currentConversion = null, $previousConversion = null)
{
    // Se os parâmetros são arrays (para métricas múltiplas)
    if (is_array($previous) && is_array($current)) {
        $growth = [];
        foreach ($current as $metric => $value) {
            $previousValue = $previous[$metric];
            $growth[$metric] = $previousValue > 0 
                ? round(($value - $previousValue) / $previousValue * 100, 1) 
                : ($value > 0 ? 100 : 0);
        }

        // Adiciona crescimento da taxa de conversão se fornecida
        if ($currentConversion !== null && $previousConversion !== null) {
            $growth['conversion'] = $previousConversion > 0
                ? round(($currentConversion - $previousConversion) / $previousConversion * 100, 1)
                : ($currentConversion > 0 ? 100 : 0);
        }

        return $growth;
    }

    // Se os parâmetros são valores únicos
    if ($previous == 0) {
        return $current > 0 ? 100 : 0;
    }
    return round((($current - $previous) / $previous) * 100, 1);
}
    private function generateInsights($growth, $devices, $locations, $keywords)
    {
        $insights = [];
    
        // Insights de crescimento
        foreach ($growth as $metric => $value) {
            $metricName = [
                'views' => 'visualizações',
                'clicks' => 'cliques',
                'calls' => 'chamadas',
                'conversion' => 'taxa de conversão'
            ][$metric] ?? $metric;
    
            if ($value > 0) {
                $insights[] = "Aumento de {$value}% em {$metricName} comparado ao período anterior.";
            } elseif ($value < 0) {
                $insights[] = "Redução de " . abs($value) . "% em {$metricName} comparado ao período anterior.";
            }
        }
    
        // Insight de dispositivos
        if (!empty($devices) && array_sum($devices) > 0) {
            arsort($devices);
            $topDevice = key($devices);
            $deviceSum = array_sum($devices);
            
            if ($deviceSum > 0) {
                $topDevicePercentage = round($devices[$topDevice] / $deviceSum * 100, 1);
    
                $deviceNames = [
                    'desktop' => 'Desktop',
                    'mobile' => 'Mobile',
                    'tablet' => 'Tablet'
                ];
    
                $deviceName = $deviceNames[$topDevice] ?? $topDevice;
                $insights[] = "{$deviceName} é o dispositivo mais usado, representando {$topDevicePercentage}% dos acessos.";
            }
        }
    
        // Insight de localização
        if (!empty($locations) && array_sum($locations) > 0) {
            arsort($locations);
            $topLocations = $locations;
            $topLocation = key($topLocations);
            $locationSum = array_sum($locations);
            
            if ($locationSum > 0) {
                $topLocationPercentage = round($topLocations[$topLocation] / $locationSum * 100, 1);
                $insights[] = "{$topLocation} é a principal origem dos acessos, com {$topLocationPercentage}% do total.";
    
                if (count($topLocations) > 1) {
                    $otherLocations = array_keys(array_slice($topLocations, 1, 2));
                    $insights[] = "Outras regiões relevantes: " . implode(', ', $otherLocations) . ".";
                }
            }
        }
    
        // Insight de palavras-chave
        if (!empty($keywords)) {
            arsort($keywords);
            $topKeywords = array_slice($keywords, 0, 3);
            
            if (!empty($topKeywords)) {
                $insights[] = "Principais termos de busca: " . implode(', ', array_keys($topKeywords)) . ".";
            }
        }
    
        return $insights;
    }

    public function performance(Request $request, Business $business)
    {
        // Verifica se o usuário tem acesso a este negócio
        if ($business->user_id !== auth()->id()) {
            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para acessar este negócio.');
        }

        // Dados de performance dos últimos 12 meses
        $performanceData = $this->getPerformanceData($business->id);
        $businesses = auth()->user()->businesses;

        return view('analytics.performance', compact(
            'businesses',
            'business',
            'performanceData'
        ));
    }

    private function getPerformanceData($businessId)
    {
        $startDate = Carbon::now()->startOfMonth()->subMonths(11);
        $endDate = Carbon::now()->endOfMonth();

        $analytics = BusinessAnalytics::where('business_id', $businessId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->groupBy(function ($item) {
                return $item->date->format('Y-m');
            });

        $months = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $yearMonth = $currentDate->format('Y-m');
            $monthData = $analytics->get($yearMonth, collect([]));

            $months[$yearMonth] = [
                'month' => $currentDate->format('M/Y'),
                'views' => $monthData->sum('views'),
                'clicks' => $monthData->sum('clicks'),
                'calls' => $monthData->sum('calls'),
                'conversion_rate' => $monthData->sum('views') > 0
                    ? round(($monthData->sum('clicks') + $monthData->sum('calls')) / $monthData->sum('views') * 100, 2)
                    : 0
            ];

            $currentDate->addMonth();
        }

        return $months;
    }

    public function refreshAnalysis(Business $business)
{
    $analytics = $this->getAnalyticsData($business->id, now()->subDays(30), now());
    $analysis = $this->geminiService->analyzeBusinessData($business, $analytics);
    
    Cache::put("business_{$business->id}_analysis", $analysis, now()->addHours(24));
    
    return response()->json([
        'success' => true,
        'analysis' => $analysis
    ]);
}

public function analyzeCompetitors($business, $competitors)
{
    // Enriquecer dados dos concorrentes com métricas do Google My Business
    $enrichedCompetitors = array_map(function($competitor) {
        return $this->enrichCompetitorData($competitor);
    }, $competitors);

    // Ordenar por relevância
    usort($enrichedCompetitors, function($a, $b) {
        return $b['relevance_score'] <=> $a['relevance_score'];
    });

    // Limitar aos top 10
    $enrichedCompetitors = array_slice($enrichedCompetitors, 0, 10);

    return [
        'competitors' => $enrichedCompetitors,
        'market_analysis' => $this->generateMarketAnalysis($business, $enrichedCompetitors),
        'recommendations' => $this->generateRecommendations($business, $enrichedCompetitors)
    ];
}

private function enrichCompetitorData($competitor)
{
    // Adicionar dados do Google My Business
    $placeId = $competitor['googlePlace']['place_id'] ?? null;
    if ($placeId) {
        $gmb_data = $this->googleBusinessService->getPlaceDetails($placeId);
        
        return array_merge($competitor, [
            'name' => $gmb_data['name'] ?? $competitor['title'],
            'address' => $gmb_data['formatted_address'] ?? 'Localização não disponível',
            'rating' => $gmb_data['rating'] ?? 0,
            'reviews' => $gmb_data['user_ratings_total'] ?? 0,
            'phone' => $gmb_data['formatted_phone_number'] ?? '',
            'website' => $gmb_data['website'] ?? '',
            'relevance_score' => $this->calculateRelevanceScore($gmb_data)
        ]);
    }

    return $competitor;
}

private function buildCompetitorAnalysisPrompt($business, $mainBusinessData, $competitorsData)
{
    // Formatar dados principais do negócio
    $mainMetrics = "Métricas do negócio principal ({$business->name}):
    - Visualizações totais: {$mainBusinessData['total_views']}
    - Cliques totais: {$mainBusinessData['total_clicks']}
    - Chamadas totais: {$mainBusinessData['total_calls']}
    - Taxa de conversão: {$mainBusinessData['conversion_rate']}%
    - Tendência de visualizações: {$mainBusinessData['trend']['views']}%
    - Tendência de cliques: {$mainBusinessData['trend']['clicks']}%
    - Distribuição de dispositivos: " . json_encode($mainBusinessData['devices']);

    // Formatar dados dos concorrentes
    $competitorsInfo = "Dados dos concorrentes:\n";
    foreach ($competitorsData as $id => $competitor) {
        $competitorsInfo .= "\nConcorrente: {$competitor['name']}
        - Website: {$competitor['website']}
        - Visualizações totais: {$competitor['total_views']}
        - Taxa de conversão: {$competitor['conversion_rate']}%
        - Tendência de visualizações: {$competitor['trend']['views']}%";
    }

    // Construir prompt completo
    return "Analise detalhadamente os seguintes dados de mercado:

    NEGÓCIO PRINCIPAL:
    Nome: {$business->name}
    Segmento: {$business->segment}
    Localização: {$business->address}

    {$mainMetrics}

    {$competitorsInfo}

    Por favor, forneça uma análise detalhada incluindo:
    1. Posicionamento atual do negócio no mercado
    2. Principais vantagens competitivas identificadas
    3. Áreas que precisam de melhorias
    4. Estratégias específicas que estão funcionando para os concorrentes
    5. Recomendações práticas baseadas nos dados apresentados
    6. Oportunidades de mercado identificadas
    7. Sugestões para aumentar a taxa de conversão

    Formate a resposta em tópicos claros e acionáveis.";
}
private function prepareCompetitorData($analytics)
{
    $totalViews = $analytics->sum('views');
    $totalClicks = $analytics->sum('clicks');
    $totalCalls = $analytics->sum('calls');
    $daysCount = max($analytics->count(), 1);

    $avgViews = round($totalViews / $daysCount, 1);
    $avgClicks = round($totalClicks / $daysCount, 1);
    $avgCalls = round($totalCalls / $daysCount, 1);

    $conversionRate = $totalViews > 0 
        ? round((($totalClicks + $totalCalls) / $totalViews) * 100, 1) 
        : 0;

    // Calcular tendência
    $trend = [
        'views' => 0,
        'clicks' => 0,
        'calls' => 0
    ];

    if ($analytics->count() >= 2) {
        $halfPoint = floor($analytics->count() / 2);
        $firstHalf = $analytics->take($halfPoint);
        $secondHalf = $analytics->skip($halfPoint);

        $trend = [
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
            )
        ];
    }

    // Obter dados de dispositivos do último registro
    $devices = $analytics->last() ? $analytics->last()->devices : [
        'desktop' => 0,
        'mobile' => 0,
        'tablet' => 0
    ];

    // Preparar dados diários
    $dailyData = $analytics->map(function($item) {
        return [
            'date' => $item->date->format('d/m'),
            'views' => $item->views,
            'clicks' => $item->clicks,
            'calls' => $item->calls,
            'visits' => $item->visits ?? 0,
            'conversion' => $item->clicks > 0 ? round(($item->calls / $item->clicks) * 100, 1) : 0
        ];
    })->toArray();

    return [
        'total_views' => $totalViews,
        'total_clicks' => $totalClicks,
        'total_calls' => $totalCalls,
        'avg_views' => $avgViews,
        'avg_clicks' => $avgClicks,
        'avg_calls' => $avgCalls,
        'conversion_rate' => $conversionRate,
        'daily_data' => $dailyData,
        'trend' => $trend,
        'devices' => $devices
    ];
}

public function updateGeminiAnalysis(Business $business)
{
    try {
        // Verificar permissão
        if ($business->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Não autorizado'
            ], 403);
        }

        // Buscar dados analíticos
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(30);
        
        $businessAnalytics = BusinessAnalytics::where('business_id', $business->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        // Preparar dados
        $mainBusinessData = $this->prepareCompetitorData($businessAnalytics);

        // Buscar concorrentes
        $competitors = $business->competitors()
            ->with(['analytics' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get();

        $competitorsData = [];
        foreach ($competitors as $competitor) {
            $competitorsData[$competitor->id] = array_merge(
                [
                    'name' => $competitor->name,
                    'website' => $competitor->website,
                    'segment' => $competitor->segment,
                    'address' => $competitor->address
                ],
                $this->prepareCompetitorData($competitor->analytics)
            );
        }

        // Gerar análise
        $prompt = $this->buildCompetitorAnalysisPrompt($business, $mainBusinessData, $competitorsData);
        $analysis = $this->geminiService->generateContent($prompt);

        // Estruturar resposta
        return response()->json([
            'success' => true,
            'market_overview' => $analysis['content'],
            'competitor_insights' => [
                'Análise de mercado atualizada com sucesso.',
                'Dados processados para ' . count($competitorsData) . ' concorrentes.',
                'Período analisado: últimos 30 dias'
            ],
            'recommendations' => [
                'Mantenha o monitoramento constante dos concorrentes',
                'Avalie as tendências identificadas',
                'Implemente as sugestões fornecidas'
            ],
            'updated_at' => now()->format('Y-m-d H:i:s')
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro na atualização da análise: ' . $e->getMessage());
        return response()->json([
            'error' => 'Erro ao atualizar análise',
            'message' => $e->getMessage()
        ], 500);
    }
}


protected function getTopLocations($analytics)
{
    // Initialize empty locations array
    $locations = [];
    
    // Loop through analytics to collect all locations
    foreach ($analytics as $record) {
        if (!empty($record->user_locations)) {
            foreach ($record->user_locations as $location => $count) {
                if (!isset($locations[$location])) {
                    $locations[$location] = 0;
                }
                $locations[$location] += $count;
            }
        }
    }

    // Sort locations by count in descending order
    arsort($locations);
    
    // Return top 5 locations
    return array_slice($locations, 0, 5, true);
}

protected function calculateTrends($analytics)
{
    if ($analytics->isEmpty()) {
        return [
            'views' => 0,
            'clicks' => 0,
            'calls' => 0,
            'conversion' => 0
        ];
    }

    // Get the first and last analytics records
    $oldest = $analytics->first();
    $latest = $analytics->last();

    // Calculate percentage changes
    $viewsTrend = $oldest->views > 0 ? 
        (($latest->views - $oldest->views) / $oldest->views) * 100 : 0;
    
    $clicksTrend = $oldest->clicks > 0 ? 
        (($latest->clicks - $oldest->clicks) / $oldest->clicks) * 100 : 0;
    
    $callsTrend = $oldest->calls > 0 ? 
        (($latest->calls - $oldest->calls) / $oldest->calls) * 100 : 0;

    // Calculate conversion rates
    $oldConversion = $oldest->views > 0 ? 
        (($oldest->clicks + $oldest->calls) / $oldest->views) * 100 : 0;
    
    $newConversion = $latest->views > 0 ? 
        (($latest->clicks + $latest->calls) / $latest->views) * 100 : 0;
    
    $conversionTrend = $oldConversion > 0 ? 
        (($newConversion - $oldConversion) / $oldConversion) * 100 : 0;

    return [
        'views' => round($viewsTrend, 1),
        'clicks' => round($clicksTrend, 1),
        'calls' => round($callsTrend, 1),
        'conversion' => round($conversionTrend, 1)
    ];
}

protected function generateSuggestions($analyticsData, Business $business)
{
    $suggestions = [];

    // Check views trend
    if (isset($analyticsData['trends']['views']) && $analyticsData['trends']['views'] < 0) {
        $suggestions[] = [
            'type' => 'warning',
            'message' => 'Suas visualizações diminuíram. Considere atualizar suas palavras-chave e descrição do negócio.'
        ];
    }

    // Check conversion rate
    if (isset($analyticsData['conversion_rate']) && $analyticsData['conversion_rate'] < 2) {
        $suggestions[] = [
            'type' => 'improvement',
            'message' => 'Sua taxa de conversão está baixa. Tente adicionar mais fotos e informações ao seu perfil.'
        ];
    }

    // Check business hours optimization
    $businessHours = $business->settings['business_hours'] ?? null;
    if ($businessHours && isset($businessHours['sunday']) && $businessHours['sunday'] === ['closed']) {
        $suggestions[] = [
            'type' => 'opportunity',
            'message' => 'Considere abrir aos domingos para aumentar sua visibilidade e atender mais clientes.'
        ];
    }

    // Check social media presence
    $socialMedia = $business->settings['social_media'] ?? [];
    $missingSocialMedia = array_diff(['facebook', 'instagram', 'twitter'], array_keys($socialMedia));
    if (!empty($missingSocialMedia)) {
        $suggestions[] = [
            'type' => 'improvement',
            'message' => 'Adicione suas redes sociais faltantes: ' . implode(', ', $missingSocialMedia)
        ];
    }

    // Device optimization suggestions
    if (isset($analyticsData['devices'])) {
        $devices = $analyticsData['devices'];
        if (isset($devices['mobile']) && $devices['mobile'] > 60) {
            $suggestions[] = [
                'type' => 'optimization',
                'message' => 'Grande parte dos seus acessos é via mobile. Certifique-se que seu site está otimizado para dispositivos móveis.'
            ];
        }
    }

    // Add default suggestions if none were generated
    if (empty($suggestions)) {
        $suggestions[] = [
            'type' => 'general',
            'message' => 'Continue mantendo seu perfil atualizado e respondendo às avaliações dos clientes.'
        ];
    }

    return $suggestions;
}
private function getKeywordAnalytics($business, $startDate, $endDate)
{
    try {
        // Busca palavras-chave do Google My Business
        $keywords = $this->googleBusinessService->getSearchKeywords(
            $business->google_business_id,
            $startDate,
            $endDate
        );

        // Organiza as palavras-chave por frequência
        $keywordStats = [];
        foreach ($keywords as $keyword) {
            $term = strtolower($keyword['term']);
            if (!isset($keywordStats[$term])) {
                $keywordStats[$term] = 0;
            }
            $keywordStats[$term] += $keyword['count'];
        }

        // Ordena por frequência e pega os top 10
        arsort($keywordStats);
        return array_slice($keywordStats, 0, 10, true);

    } catch (\Exception $e) {
        \Log::error('Erro ao buscar palavras-chave: ' . $e->getMessage());
        return [];
    }
}

}