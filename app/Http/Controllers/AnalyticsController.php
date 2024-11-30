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

class AnalyticsController extends Controller
{
    public function index(Business $business)
    {
        if (!$business) {
            return redirect()
                ->route('business.create')
                ->with('warning', 'Você precisa cadastrar um negócio primeiro.');
        }

        return redirect()->route('analytics.dashboard', [
            'business' => $business->id
        ]);
    }

    public function dashboard(Business $business)
    {
        if ($business->user_id !== auth()->id()) {
            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para acessar este negócio.');
        }

        $period = request()->input('period', 30);
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($period);

        if (request()->has('start_date') && request()->has('end_date')) {
            $startDate = Carbon::parse(request()->start_date);
            $endDate = Carbon::parse(request()->end_date);
        }

        $analyticsData = $this->getAnalyticsData($business->id, $startDate, $endDate);
        $businesses = auth()->user()->businesses;

        return view('analytics.dashboard', array_merge(
            $analyticsData,
            [
                'businesses' => $businesses,
                'selectedBusiness' => $business,
            ]
        ));
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

        $totalViews = $mainBusinessData['total_views'];
        $totalClicks = $mainBusinessData['total_clicks'];
        $totalCalls = $mainBusinessData['total_calls'];

        foreach ($competitorsData as $data) {
            $totalViews += $data['total_views'];
            $totalClicks += $data['total_clicks'];
            $totalCalls += $data['total_calls'];
        }

        $mainBusinessData['market_share'] = [
            'views' => $totalViews > 0 ? round(($mainBusinessData['total_views'] / $totalViews) * 100, 1) : 0,
            'clicks' => $totalClicks > 0 ? round(($mainBusinessData['total_clicks'] / $totalClicks) * 100, 1) : 0,
            'calls' => $totalCalls > 0 ? round(($mainBusinessData['total_calls'] / $totalCalls) * 100, 1) : 0
        ];

        foreach ($competitorsData as &$data) {
            $data['market_share'] = [
                'views' => $totalViews > 0 ? round(($data['total_views'] / $totalViews) * 100, 1) : 0,
                'clicks' => $totalClicks > 0 ? round(($data['total_clicks'] / $totalClicks) * 100, 1) : 0,
                'calls' => $totalCalls > 0 ? round(($data['total_calls'] / $totalCalls) * 100, 1) : 0
            ];
        }

        $competitorInsights = $this->generateCompetitorInsights($mainBusinessData, $competitorsData);
        $businesses = auth()->user()->businesses;

        return view('analytics.competitors', compact(
            'businesses',
            'business',
            'mainBusinessData',
            'competitorsData',
            'startDate',
            'endDate',
            'competitorInsights'
        ));
    }

    private function generateCompetitorInsights($mainBusinessData, $competitorsData)
    {
        $insights = [];
        
        $marketShareViews = $mainBusinessData['market_share']['views'];
        if ($marketShareViews > 50) {
            $insights[] = "Seu negócio é líder em visualizações com {$marketShareViews}% do mercado.";
        } elseif ($marketShareViews < 30) {
            $insights[] = "Oportunidade de crescimento: sua participação nas visualizações está em {$marketShareViews}%.";
        }

        $avgCompetitorConversion = collect($competitorsData)->avg('conversion_rate');
        if ($mainBusinessData['conversion_rate'] > $avgCompetitorConversion) {
            $difference = round($mainBusinessData['conversion_rate'] - $avgCompetitorConversion, 1);
            $insights[] = "Sua taxa de conversão está {$difference}% acima da média dos concorrentes.";
        } else {
            $difference = round($avgCompetitorConversion - $mainBusinessData['conversion_rate'], 1);
            $insights[] = "Oportunidade de melhoria: sua taxa de conversão está {$difference}% abaixo da média.";
        }

        $mainMobileShare = $mainBusinessData['devices']['mobile'] ?? 0;
        $avgCompetitorMobile = collect($competitorsData)->avg(function($competitor) {
            return $competitor['devices']['mobile'] ?? 0;
        });
        
        if (abs($mainMobileShare - $avgCompetitorMobile) > 10) {
            $insights[] = "Sua distribuição de dispositivos móveis difere significativamente da concorrência.";
        }

        if ($mainBusinessData['trend']['views'] > 0 && $mainBusinessData['trend']['clicks'] > 0) {
            $insights[] = "Seu negócio está em crescimento tanto em visualizações quanto em cliques.";
        } elseif ($mainBusinessData['trend']['views'] < 0 && $mainBusinessData['trend']['clicks'] < 0) {
            $insights[] = "Atenção: tendência de queda em visualizações e cliques.";
        }

        return $insights;
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

        $dailyData = [];
        foreach ($analytics as $record) {
            $date = Carbon::parse($record->date)->format('d/m');
            $dailyData[$date] = [
                'views' => $record->views,
                'clicks' => $record->clicks,
                'calls' => $record->calls
            ];
        }

        $trend = $this->calculateTrend($analytics);

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
            'devices' => $analytics->last()?->devices ?? [
                'desktop' => 0,
                'mobile' => 0,
                'tablet' => 0
            ]
        ];
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

    $dates = $analytics->pluck('date')->map(fn($date) => $date->format('d/m'))->toArray();
    $views = $analytics->pluck('views')->toArray();
    $clicks = $analytics->pluck('clicks')->toArray();
    $calls = $analytics->pluck('calls')->toArray();

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

    // Calculate average rating (you may need to adjust this based on your data source)
    $averageRating = $lastAnalytics && isset($lastAnalytics->rating) 
        ? $lastAnalytics->rating 
        : 0;

    $growth = $this->calculateGrowth($currentTotal, $previousTotal);

    // ... rest of the existing code ...

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
        'averageRating'  // Add this line
    );
}

private function calculateGrowth($current, $previous)
{
    $growth = [];

    foreach ($current as $metric => $value) {
        $previousValue = $previous[$metric];
        $growth[$metric] = $previousValue > 0 
            ? round(($value - $previousValue) / $previousValue * 100, 1) 
            : 0;
    }

    // Add conversion rate growth
    $growth['conversion'] = $previousConversion > 0
        ? round(($currentConversion - $previousConversion) / $previousConversion * 100, 1)
        : 0;

    // Add rating growth (if applicable)
    $growth['rating'] = 0; // You may want to calculate this based on your rating data

    return $growth;
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
        if (!empty($devices)) {
            arsort($devices);
            $topDevice = key($devices);
            $topDevicePercentage = round($devices[$topDevice] / array_sum($devices) * 100, 1);
            
            $deviceNames = [
                'desktop' => 'Desktop',
                'mobile' => 'Mobile',
                'tablet' => 'Tablet'
            ];
            
            $deviceName = $deviceNames[$topDevice] ?? $topDevice;
            $insights[] = "{$deviceName} é o dispositivo mais usado, representando {$topDevicePercentage}% dos acessos.";
        }

        // Insight de localização
        if (!empty($locations)) {
            arsort($locations);
            $topLocations = array_slice($locations, 0, 3, true);
            
            if (count($topLocations) > 0) {
                $topLocation = key($topLocations);
                $topLocationPercentage = round($topLocations[$topLocation] / array_sum($locations) * 100, 1);
                $insights[] = "{$topLocation} é a principal origem dos acessos, com {$topLocationPercentage}% do total.";
                
                if (count($topLocations) > 1) {
                    $otherLocations = array_keys(array_slice($topLocations, 1, 2));
                    $insights[] = "Outras cidades relevantes: " . implode(' e ', $otherLocations) . ".";
                }
            }
        }

        // Insight de palavras-chave
        if (!empty($keywords)) {
            arsort($keywords);
            $topKeywords = array_slice($keywords, 0, 3, true);
            
            if (count($topKeywords) > 0) {
                $keywordsList = implode(', ', array_keys($topKeywords));
                $insights[] = "Principais termos de busca: {$keywordsList}.";
                
                $topKeyword = key($topKeywords);
                $topKeywordPercentage = round($topKeywords[$topKeyword] / array_sum($keywords) * 100, 1);
                $insights[] = "O termo \"{$topKeyword}\" representa {$topKeywordPercentage}% das buscas.";
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
}