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
            @apply bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow dark:bg-gray-800;
        }

        .chart-container {
            @apply bg-white rounded-lg shadow-sm p-6 dark:bg-gray-800;
        }

        .trend-indicator {
            @apply inline-flex items-center px-2 py-1 rounded-full text-sm;
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
            @apply animate-spin rounded-full h-12 w-12 border-4 border-t-blue-500;
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-xl font-google-sans text-gray-800 dark:text-gray-200">
                Analytics - {{ $business->name }}
            </h2>
            
            <div class="flex items-center space-x-4">
                <!-- Seletor de Período -->
                <select id="period-selector" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30" selected>Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                    <option value="custom">Período personalizado</option>
                </select>

                <!-- Seletor de Negócio -->
                <select id="business-selector" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    @foreach($businesses as $b)
                        <option value="{{ $b->id }}" {{ $b->id == $business->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Botão de Exportar -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center">
                        Exportar
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 dark:bg-gray-700">
                        <div class="py-1">
                            <a href="{{ route('analytics.export.pdf', $business->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                Exportar PDF
                            </a>
                            <a href="{{ route('analytics.export.excel', $business->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                Exportar Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Link para Análise de Concorrentes -->
                <a href="{{ route('analytics.competitors', $business->id) }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                    Análise de Concorrentes
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Grid de Métricas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Visualizações -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600 dark:text-gray-300">Visualizações</h3>
                    <p class="text-3xl font-google-sans mt-2 dark:text-gray-200">{{ number_format($metrics['views']) }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['views'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['views'] }}%
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Cliques -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600 dark:text-gray-300">Cliques</h3>
                    <p class="text-3xl font-google-sans mt-2 dark:text-gray-200">{{ number_format($metrics['clicks']) }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['clicks'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['clicks'] }}%
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Ligações -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600 dark:text-gray-300">Ligações</h3>
                    <p class="text-3xl font-google-sans mt-2 dark:text-gray-200">{{ number_format($metrics['calls']) }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['calls'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['calls'] }}%
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Visitas ao Local -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600 dark:text-gray-300">Visitas ao Local</h3>
                    <p class="text-3xl font-google-sans mt-2 dark:text-gray-200">
                        {{ number_format($metrics['visits'] ?? 0) }}
                    </p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ isset($trends['visits']) && $trends['visits'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['visits'] ?? 0 }}%
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">vs. período anterior</span>
                    </div>
                </div>
                            <!-- Gráficos e Análises Avançadas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Gráfico de Performance ao Longo do Tempo -->
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Performance ao Longo do Tempo</h3>
                    <div class="relative" style="height: 400px;">
                        <canvas id="performanceChart"></canvas>
                        <div class="loading-overlay hidden">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Dispositivos -->
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Distribuição por Dispositivos</h3>
                    <div class="relative" style="height: 400px;">
                        <canvas id="devicesChart"></canvas>
                        <div class="loading-overlay hidden">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>

                <!-- Origem do Tráfego -->
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Origem do Tráfego</h3>
                    <div class="relative" style="height: 400px;">
                        <canvas id="trafficSourceChart"></canvas>
                        <div class="loading-overlay hidden">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>

                <!-- Horários de Maior Movimento -->
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Horários de Maior Movimento</h3>
                    <div class="relative" style="height: 400px;">
                        <canvas id="peakHoursChart"></canvas>
                        <div class="loading-overlay hidden">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análise de Localização -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h3 class="text-lg font-google-sans text-gray-800 mb-4">Análise de Localização</h3>
                <div id="locationMap" style="height: 400px;"></div>
            </div>

            <!-- Insights e Recomendações -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Insights -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Insights</h3>
                    <div class="space-y-4">
                        @foreach($insights as $insight)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-600">{{ $insight }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recomendações -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Recomendações</h3>
                    <div class="space-y-4">
                        @foreach($recommendations as $recommendation)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-600">{{ $recommendation }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=visualization"></script>
    
    <script>
        // Configurações globais do Chart.js
        Chart.defaults.font.family = 'Google Sans';
        Chart.defaults.color = '#374151';
        Chart.register(ChartDataLabels);

        // Função para atualizar dados
        async function refreshData() {
            try {
                const response = await fetch(`/api/analytics/refresh/${businessId}`);
                const data = await response.json();
                
                if (data.success) {
                    updateCharts(data);
                    updateMetrics(data);
                }
            } catch (error) {
                console.error('Erro ao atualizar dados:', error);
            }
        }

// Gráfico de Performance
const performanceChart = new Chart(
    document.getElementById('performanceChart').getContext('2d'),
    {
        type: 'line',
        data: {
            labels: {!! json_encode($analyticsData['dates']) !!}, // Modificado aqui
            datasets: [
                {
                    label: 'Visualizações',
                    data: {!! json_encode($analyticsData['views']) !!},
                    borderColor: '#4285F4',
                    tension: 0.4
                },
                {
                    label: 'Cliques',
                    data: {!! json_encode($analyticsData['clicks']) !!},
                    borderColor: '#0F9D58',
                    tension: 0.4
                },
                {
                    label: 'Ligações',
                    data: {!! json_encode($analyticsData['calls']) !!},
                    borderColor: '#DB4437',
                    tension: 0.4
                }
            ]
        },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
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
            }
        );

        // Gráfico de Dispositivos
        const devicesChart = new Chart(
            document.getElementById('devicesChart').getContext('2d'),
            {
                type: 'doughnut',
                data: {
                    labels: ['Desktop', 'Mobile', 'Tablet'],
                    datasets: [{
                        data: [
                            {{ $metrics['devices']['desktop'] }},
                            {{ $metrics['devices']['mobile'] }},
                            {{ $metrics['devices']['tablet'] }}
                        ],
                        backgroundColor: ['#4285F4', '#DB4437', '#F4B400']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        datalabels: {
                            formatter: (value) => {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        );

        // Inicialização do mapa
        function initMap() {
            const map = new google.maps.Map(document.getElementById('locationMap'), {
                zoom: 10,
                center: { lat: {{ $business->latitude }}, lng: {{ $business->longitude }} }
            });

            const heatmapData = {!! json_encode($locationData) !!}.map(point => ({
                location: new google.maps.LatLng(point.lat, point.lng),
                weight: point.weight
            }));

            new google.maps.visualization.HeatmapLayer({
                data: heatmapData,
                map: map
            });
        }

        // Event Listeners
        document.getElementById('period-selector').addEventListener('change', function(e) {
            window.location.href = `/analytics/${businessId}?period=${e.target.value}`;
        });

        document.getElementById('business-selector').addEventListener('change', function(e) {
            window.location.href = `/analytics/${e.target.value}`;
        });

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            setInterval(refreshData, 300000); // Atualiza a cada 5 minutos
        });
    </script>
    @endpush
</x-app-layout>