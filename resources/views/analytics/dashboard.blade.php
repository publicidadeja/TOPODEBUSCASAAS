<x-app-layout>
    @push('styles')
    <style>
        :root {
            --google-blue: #4285F4;
            --google-red: #DB4437;
            --google-yellow: #F4B400;
            --google-green: #0F9D58;
        }

        .metric-card {
            @apply bg-white rounded-lg shadow-sm p-4 md:p-6 hover:shadow-md transition-shadow dark:bg-gray-800 flex flex-col h-full;
        }

        .chart-container {
            @apply bg-white rounded-lg shadow-sm p-4 md:p-6 dark:bg-gray-800 h-full;
        }

        .trend-indicator {
            @apply inline-flex items-center px-2 py-1 rounded-full text-xs md:text-sm;
        }

        .trend-up {
            @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200;
        }

        .trend-down {
            @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200;
        }
        
        .loading-overlay {
            @apply absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center dark:bg-gray-800 dark:bg-opacity-75;
        }

        .loading-spinner {
            @apply animate-spin rounded-full h-8 w-8 md:h-12 md:w-12 border-4 border-t-blue-500;
        }

        .dashboard-header {
            @apply sticky top-0 z-10 bg-white dark:bg-gray-900 shadow-sm;
        }

        .dashboard-controls {
            @apply flex flex-col md:flex-row gap-3 md:gap-4 items-stretch md:items-center;
        }

        .select-control {
            @apply w-full md:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200;
        }

        .btn-primary {
            @apply bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center justify-center;
        }

        .btn-success {
            @apply bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition flex items-center justify-center;
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="dashboard-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h2 class="text-xl font-google-sans text-gray-800 dark:text-gray-200">
                        Analytics - {{ $business->name }}
                    </h2>
                    
                    <div class="dashboard-controls w-full md:w-auto">
                        <select id="period-selector" class="select-control">
                            <option value="7">Últimos 7 dias</option>
                            <option value="30" selected>Últimos 30 dias</option>
                            <option value="90">Últimos 90 dias</option>
                            <option value="custom">Período personalizado</option>
                        </select>

                        <select id="business-selector" class="select-control">
                            @foreach($businesses as $b)
                                <option value="{{ $b->id }}" {{ $b->id == $business->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="btn-primary w-full md:w-auto">
                                Exportar
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 dark:bg-gray-700">
                                <div class="py-1">
                                    <a href="{{ route('analytics.export.pdf', $business->id) }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                        Exportar PDF
                                    </a>
                                    <a href="{{ route('analytics.export.excel', $business->id) }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                        Exportar Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('analytics.competitors', $business->id) }}" 
                           class="btn-success w-full md:w-auto">
                            Análise de Concorrentes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
    <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Grid de métricas principais -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Visualizações -->
            <div class="metric-card">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-gray-600 font-google-sans">Visualizações</h4>
                    <div class="trend-indicator {{ $metrics['views_trend'] >= 0 ? 'trend-up' : 'trend-down' }}">
                        <span>{{ $metrics['views_trend'] }}%</span>
                    </div>
                </div>
                <p class="text-3xl font-google-sans mb-2">{{ number_format($metrics['total_views']) }}</p>
                <span class="text-sm text-gray-500">vs. período anterior</span>
            </div>

            <!-- Cliques -->
            <div class="metric-card">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-gray-600 font-google-sans">Cliques</h4>
                    <div class="trend-indicator {{ $metrics['clicks_trend'] >= 0 ? 'trend-up' : 'trend-down' }}">
                        <span>{{ $metrics['clicks_trend'] }}%</span>
                    </div>
                </div>
                <p class="text-3xl font-google-sans mb-2">{{ number_format($metrics['total_clicks']) }}</p>
                <span class="text-sm text-gray-500">vs. período anterior</span>
            </div>

            <!-- Ligações -->
            <div class="metric-card">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-gray-600 font-google-sans">Ligações</h4>
                    <div class="trend-indicator {{ $metrics['calls_trend'] >= 0 ? 'trend-up' : 'trend-down' }}">
                        <span>{{ $metrics['calls_trend'] }}%</span>
                    </div>
                </div>
                <p class="text-3xl font-google-sans mb-2">{{ number_format($metrics['total_calls']) }}</p>
                <span class="text-sm text-gray-500">vs. período anterior</span>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gráfico de Tendências -->
            <div class="chart-container">
                <h3 class="text-lg font-google-sans mb-4">Tendências</h3>
                <div class="relative h-[300px] md:h-[400px]">
                    <canvas id="trendsChart"></canvas>
                    <div class="loading-overlay hidden" id="trendsChartLoader">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Conversões -->
            <div class="chart-container">
                <h3 class="text-lg font-google-sans mb-4">Conversões</h3>
                <div class="relative h-[300px] md:h-[400px]">
                    <canvas id="conversionsChart"></canvas>
                    <div class="loading-overlay hidden" id="conversionsChartLoader">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Insights -->
        <div class="bg-white rounded-lg shadow-sm p-6 dark:bg-gray-800">
            <h3 class="text-lg font-google-sans mb-4">Insights</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="insights-container">
            @foreach($insights as $insight)
<div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $insight }}</p>
        </div>
    </div>
</div>
@endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Configurações globais do Chart.js
    Chart.defaults.font.family = 'Google Sans';
    Chart.defaults.color = '#666';
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;

    // Função para mostrar/esconder loader
    function toggleLoader(chartId, show) {
        const loader = document.getElementById(`${chartId}Loader`);
        loader.classList.toggle('hidden', !show);
    }

    // Função para atualizar dados
    async function updateChartData(period, businessId) {
        toggleLoader('trendsChart', true);
        toggleLoader('conversionsChart', true);

        try {
            const response = await fetch(`/analytics/data/${businessId}?period=${period}`);
            const data = await response.json();
            
            // Atualiza gráfico de tendências
            trendsChart.data = {
                labels: data.trends.labels,
                datasets: [
                    {
                        label: 'Visualizações',
                        data: data.trends.views,
                        borderColor: '#4285F4',
                        backgroundColor: '#4285F4',
                        tension: 0.4
                    },
                    {
                        label: 'Cliques',
                        data: data.trends.clicks,
                        borderColor: '#0F9D58',
                        backgroundColor: '#0F9D58',
                        tension: 0.4
                    },
                    {
                        label: 'Ligações',
                        data: data.trends.calls,
                        borderColor: '#F4B400',
                        backgroundColor: '#F4B400',
                        tension: 0.4
                    }
                ]
            };
            trendsChart.update();

            // Atualiza gráfico de conversões
            conversionsChart.data = {
                labels: ['Visualizações', 'Cliques', 'Ligações'],
                datasets: [{
                    data: [
                        data.conversions.views,
                        data.conversions.clicks,
                        data.conversions.calls
                    ],
                    backgroundColor: ['#4285F4', '#0F9D58', '#F4B400']
                }]
            };
            conversionsChart.update();

            // Atualiza métricas e insights
            updateMetrics(data.metrics);
            updateInsights(data.insights);

        } catch (error) {
            console.error('Erro ao atualizar dados:', error);
        } finally {
            toggleLoader('trendsChart', false);
            toggleLoader('conversionsChart', false);
        }
    }

    // Inicialização dos gráficos
    const trendsChart = new Chart('trendsChart', {
        type: 'line',
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const conversionsChart = new Chart('conversionsChart', {
        type: 'bar',
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Event Listeners
    document.getElementById('period-selector').addEventListener('change', function(e) {
        const businessId = document.getElementById('business-selector').value;
        updateChartData(e.target.value, businessId);
    });

    document.getElementById('business-selector').addEventListener('change', function(e) {
        const period = document.getElementById('period-selector').value;
        updateChartData(period, e.target.value);
    });

    // Inicialização
    updateChartData(
        document.getElementById('period-selector').value,
        document.getElementById('business-selector').value
    );
</script>
@endpush
</x-app-layout>