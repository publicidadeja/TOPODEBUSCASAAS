<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessAnalytics;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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

        // Buscar analytics dos últimos 30 dias
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(30);

        $analytics = BusinessAnalytics::where('business_id', $selectedBusiness->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        // Calcular totais
        $totalViews = $analytics->sum('views');
        $totalClicks = $analytics->sum('clicks');
        $totalCalls = $analytics->sum('calls');

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

        // Preparar dados para o gráfico de comparação
        $compareStartDate = Carbon::now()->subDays(60);
        $previousPeriodAnalytics = BusinessAnalytics::where('business_id', $selectedBusiness->id)
            ->whereBetween('date', [$compareStartDate, $startDate])
            ->orderBy('date')
            ->get();

        // Calcular totais do período anterior
        $previousTotalViews = $previousPeriodAnalytics->sum('views');
        $previousTotalClicks = $previousPeriodAnalytics->sum('clicks');
        $previousTotalCalls = $previousPeriodAnalytics->sum('calls');
        $previousConversionRate = $previousTotalViews > 0 
            ? round((($previousTotalClicks + $previousTotalCalls) / $previousTotalViews) * 100, 1)
            : 0;

        // Calcular variações percentuais
        $analyticsData['variations'] = [
            'views' => $previousTotalViews > 0 
                ? round((($totalViews - $previousTotalViews) / $previousTotalViews) * 100, 1)
                : 0,
            'clicks' => $previousTotalClicks > 0 
                ? round((($totalClicks - $previousTotalClicks) / $previousTotalClicks) * 100, 1)
                : 0,
            'calls' => $previousTotalCalls > 0 
                ? round((($totalCalls - $previousTotalCalls) / $previousTotalCalls) * 100, 1)
                : 0,
            'conversion' => $previousConversionRate > 0 
                ? round((($conversionRate - $previousConversionRate) / $previousConversionRate) * 100, 1)
                : 0
        ];

        // Gerar insights e sugestões
        $suggestions = $this->generateSuggestions($analyticsData, $selectedBusiness);

        return view('dashboard', [
            'businesses' => $businesses,
            'selectedBusiness' => $selectedBusiness,
            'analytics' => $analyticsData,
            'suggestions' => $suggestions
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

    private function generateSuggestions($analyticsData, $selectedBusiness)
    {
        $suggestions = [];

        // Verificar tendências negativas de visualizações
        if ($analyticsData['variations']['views'] < 0) {
            $suggestions[] = [
                'type' => 'warning',
                'message' => 'As visualizações diminuíram em comparação com o período anterior. Considere revisar suas palavras-chave e conteúdo.',
                'action' => 'Revisar SEO',
                'action_url' => route('business.edit', ['business' => $selectedBusiness->id])
            ];
        }

        // Verificar tendências de conversão
        if ($analyticsData['variations']['conversion'] < 0) {
            $suggestions[] = [
                'type' => 'warning',
                'message' => 'A taxa de conversão está menor que o período anterior. Verifique a experiência do usuário e chamadas para ação.',
                'action' => 'Ver Análise Detalhada',
                'action_url' => route('analytics', ['businessId' => $selectedBusiness->id])
            ];
        }

        // Verificar distribuição de dispositivos
        $devices = $analyticsData['devices'];
        if (($devices['mobile'] ?? 0) < 30) {
            $suggestions[] = [
                'type' => 'info',
                'message' => 'Seu site tem poucos acessos via dispositivos móveis. Certifique-se que está otimizado para smartphones.',
                'action' => 'Verificar Responsividade',
                'action_url' => $selectedBusiness->website
            ];
        }

        // Sugestões positivas
        if ($analyticsData['variations']['views'] > 20) {
            $suggestions[] = [
                'type' => 'success',
                'message' => 'Parabéns! Suas visualizações aumentaram significativamente.',
                'action' => 'Ver Detalhes',
                'action_url' => route('analytics', ['businessId' => $selectedBusiness->id])
            ];
        }

        // Verificar tendências de chamadas
        if ($analyticsData['variations']['calls'] < -10) {
            $suggestions[] = [
                'type' => 'warning',
                'message' => 'O número de chamadas diminuiu significativamente. Verifique a visibilidade do seu número de telefone.',
                'action' => 'Verificar Contatos',
                'action_url' => route('business.edit', ['business' => $selectedBusiness->id])
            ];
        }

        return $suggestions;
    }
}