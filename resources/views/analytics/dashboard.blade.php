<x-app-layout>
    @push('styles')
    <style>
        :root {
            --primary-color: #4285F4;
            --success-color: #0F9D58;
            --warning-color: #F4B400;
            --danger-color: #DB4437;
        }

        .dashboard-container {
            @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6;
        }

        .metric-grid {
            @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6;
        }

        .chart-grid {
            @apply grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6;
        }

        .metric-card {
            @apply bg-white rounded-lg shadow p-6 border border-gray-200;
        }

        .chart-container {
            @apply bg-white rounded-lg shadow p-6 border border-gray-200;
        }

        .trend-indicator {
            @apply inline-flex items-center px-2 py-1 rounded text-sm;
        }

        .trend-up {
            @apply bg-green-100 text-green-800;
        }

        .trend-down {
            @apply bg-red-100 text-red-800;
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">
                Analytics - {{ $business->name }}
            </h2>
            
            <div class="flex space-x-4">
                <select id="business-selector" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                    @foreach($businesses as $b)
                        <option value="{{ $b->id }}" {{ $b->id == $business->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>

                <select id="period-selector" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30" selected>Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                </select>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Exportar
                        <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg">
                        <a href="{{ route('analytics.export.pdf', $business->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Exportar PDF
                        </a>
                        <a href="{{ route('analytics.export.excel', $business->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Exportar Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="dashboard-container">
    <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Main Analytics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- Views Metric -->
            <div class="metric-card">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Visualizações</h3>
                <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900" id="metric-views">
                        {{ number_format($metrics['total_views']) }}
                    </span>
                    @if($metrics['trends']['views'] > 0)
                        <span class="trend-indicator trend-up ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format($metrics['trends']['views'], 1) }}%
                        </span>
                    @else
                        <span class="trend-indicator trend-down ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format(abs($metrics['trends']['views']), 1) }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Clicks Metric -->
            <div class="metric-card">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Cliques</h3>
                <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900" id="metric-clicks">
                        {{ number_format($metrics['total_clicks']) }}
                    </span>
                    @if($metrics['trends']['clicks'] > 0)
                        <span class="trend-indicator trend-up ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format($metrics['trends']['clicks'], 1) }}%
                        </span>
                    @else
                        <span class="trend-indicator trend-down ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format(abs($metrics['trends']['clicks']), 1) }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Calls Metric (NOVO) -->
            <div class="metric-card">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Ligações</h3>
                <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900" id="metric-calls">
                        {{ number_format($metrics['total_calls']) }}
                    </span>
                    @if($metrics['trends']['calls'] > 0)
                        <span class="trend-indicator trend-up ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format($metrics['trends']['calls'], 1) }}%
                        </span>
                    @else
                        <span class="trend-indicator trend-down ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format(abs($metrics['trends']['calls']), 1) }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Conversion Rate Metric -->
            <div class="metric-card">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Taxa de Conversão</h3>
                <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900" id="metric-conversion">
                        {{ number_format($metrics['conversion_rate'], 2) }}%
                    </span>
                    @if($metrics['trends']['conversion'] > 0)
                        <span class="trend-indicator trend-up ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format($metrics['trends']['conversion'], 1) }}%
                        </span>
                    @else
                        <span class="trend-indicator trend-down ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format(abs($metrics['trends']['conversion']), 1) }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Response Time Metric -->
            <div class="metric-card">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Tempo de Resposta</h3>
                <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900" id="metric-response">
                        {{ $metrics['response_time'] }}
                    </span>
                    @if($metrics['trends']['response_time'] < 0)
                        <span class="trend-indicator trend-up ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format(abs($metrics['trends']['response_time']), 1) }}%
                        </span>
                    @else
                        <span class="trend-indicator trend-down ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format($metrics['trends']['response_time'], 1) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>
        

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Performance Chart -->
            <div class="chart-container">
                <h3 class="text-lg font-semibold mb-4">Performance</h3>
                <div class="relative h-[400px]">
                    <canvas id="performanceChart"></canvas>
                    <div class="loading-overlay hidden" id="performanceChartLoader">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>

            <!-- Engagement Chart -->
            <div class="chart-container">
                <h3 class="text-lg font-semibold mb-4">Engajamento</h3>
                <div class="relative h-[400px]">
                    <canvas id="engagementChart"></canvas>
                    <div class="loading-overlay hidden" id="engagementChartLoader">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>

    <!-- Seção de Palavras-chave -->
<div class="card p-6 bg-white rounded-lg shadow-sm">
    <h3 class="text-lg font-semibold mb-4">Palavras-chave Populares</h3>
    
    @if(!empty($keywords))
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($keywords as $term => $count)
                <div class="bg-gray-50 p-3 rounded">
                    <span class="text-sm text-gray-600">{{ $term }}</span>
                    <div class="text-lg font-semibold">{{ $count }}</div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">Nenhuma palavra-chave encontrada no período.</p>
    @endif
</div>

<!-- Insights Section -->
<div class="mt-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                <span class="flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Insights Estratégicos
                </span>
            </h2>
            <span class="text-sm text-gray-500">Atualizado em tempo real</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Performance Geral -->
            <div class="bg-gradient-to-br from-blue-50 to-white rounded-lg p-4">
                <h3 class="text-lg font-semibold text-blue-800 mb-3">Performance Geral</h3>
                <div class="space-y-3">
                    @foreach($insights as $insight)
                        @if(isset($insight['type']) && $insight['type'] === 'performance')
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-gray-700">{{ $insight['message'] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Oportunidades de Melhoria -->
            <div class="bg-gradient-to-br from-green-50 to-white rounded-lg p-4">
                <h3 class="text-lg font-semibold text-green-800 mb-3">Oportunidades de Melhoria</h3>
                <div class="space-y-3">
                    @foreach($insights as $insight)
                        @if(isset($insight['type']) && $insight['type'] === 'opportunity')
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-gray-700">{{ $insight['message'] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Alertas e Recomendações -->
            <div class="bg-gradient-to-br from-yellow-50 to-white rounded-lg p-4">
                <h3 class="text-lg font-semibold text-yellow-800 mb-3">Alertas e Recomendações</h3>
                <div class="space-y-3">
                    @foreach($insights as $insight)
                        @if(isset($insight['type']) && $insight['type'] === 'alert')
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-gray-700">{{ $insight['message'] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Tendências de Mercado -->
            <div class="bg-gradient-to-br from-purple-50 to-white rounded-lg p-4">
                <h3 class="text-lg font-semibold text-purple-800 mb-3">Tendências de Mercado</h3>
                <div class="space-y-3">
                    @foreach($insights as $insight)
                        @if(isset($insight['type']) && $insight['type'] === 'trend')
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-gray-700">{{ $insight['message'] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Ações Recomendadas -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ações Recomendadas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($insights as $insight)
                    @if(isset($insight['type']) && $insight['type'] === 'action')
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $insight['title'] }}</h4>
                                    <p class="text-sm text-gray-500">{{ $insight['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    @endpush

@push('scripts')
<script>
// Chart.js Configuration and Setup
document.addEventListener('DOMContentLoaded', function() {
    // Global Chart.js Configuration
    Chart.defaults.font.family = 'Inter var, sans-serif';
    Chart.defaults.color = '#6B7280';
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;

    // Performance Chart Configuration
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($analyticsData['dates']) !!},
            datasets: [
                {
                    label: 'Visualizações',
                    data: {!! json_encode($analyticsData['views']) !!},
                    borderColor: '#4285F4',
                    backgroundColor: 'rgba(66, 133, 244, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Cliques',
                    data: {!! json_encode($analyticsData['clicks']) !!},
                    borderColor: '#0F9D58',
                    backgroundColor: 'rgba(15, 157, 88, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Engagement Chart Configuration
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    const engagementChart = new Chart(engagementCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($analyticsData['dates']) !!},
            datasets: [{
                label: 'Taxa de Conversão',
                data: {!! json_encode($analyticsData['conversionRates']) !!},
                backgroundColor: '#DB4437',
                borderRadius: 4
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Event Handlers
    const periodSelector = document.getElementById('period-selector');
    const businessSelector = document.getElementById('business-selector');

    periodSelector.addEventListener('change', async function(e) {
        const period = e.target.value;
        const businessId = businessSelector.value;
        
        try {
            showLoaders();
            const response = await fetch(`/analytics/data/${businessId}?period=${period}`);
            const data = await response.json();
            
            if (data.success) {
                updateCharts(performanceChart, engagementChart, data);
                updateMetrics(data.metrics);
                updateInsights(data.insights);
            } else {
                throw new Error(data.message || 'Erro ao atualizar dados');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Erro ao atualizar dashboard');
        } finally {
            hideLoaders();
        }
    });

    businessSelector.addEventListener('change', function(e) {
        window.location.href = `/analytics/dashboard/${e.target.value}`;
    });

    // Helper Functions
    function showLoaders() {
        document.querySelectorAll('.loading-overlay').forEach(loader => {
            loader.classList.remove('hidden');
        });
    }

    function hideLoaders() {
        document.querySelectorAll('.loading-overlay').forEach(loader => {
            loader.classList.add('hidden');
        });
    }

    function updateCharts(performanceChart, engagementChart, data) {
        // Update Performance Chart
        performanceChart.data.labels = data.dates;
        performanceChart.data.datasets[0].data = data.views;
        performanceChart.data.datasets[1].data = data.clicks;
        performanceChart.update();

        // Update Engagement Chart
        engagementChart.data.labels = data.dates;
        engagementChart.data.datasets[0].data = data.conversionRates;
        engagementChart.update();
    }

    function updateMetrics(metrics) {
        Object.keys(metrics).forEach(key => {
            const element = document.querySelector(`[data-metric="${key}"]`);
            if (element) {
                element.textContent = formatMetric(metrics[key], key);
            }
        });
    }

    function updateInsights(insights) {
        const container = document.querySelector('.insights-section');
        if (container && insights.length) {
            container.innerHTML = insights.map(insight => `
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600">${insight}</p>
                    </div>
                </div>
            `).join('');
        }
    }

    function formatMetric(value, type) {
        if (type.includes('rate')) {
            return value.toFixed(2) + '%';
        }
        return new Intl.NumberFormat().format(value);
    }

    function showError(message) {
        // Implement error notification system here
        console.error(message);
    }
});
</script>
@endpush
</x-app-layout>