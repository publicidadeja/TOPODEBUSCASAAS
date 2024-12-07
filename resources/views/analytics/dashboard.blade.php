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
            @apply bg-white rounded-lg shadow-sm p-4 md:p-6 hover:shadow-md transition-shadow flex flex-col h-full border border-gray-100;
        }

        .chart-container {
            @apply bg-white rounded-lg shadow-sm p-4 md:p-6 h-full border border-gray-100;
        }

        .trend-indicator {
            @apply inline-flex items-center px-2 py-1 rounded-full text-xs md:text-sm;
        }

        .trend-up {
            @apply bg-green-50 text-green-600;
        }

        .trend-down {
            @apply bg-red-50 text-red-600;
        }
        
        .loading-overlay {
            @apply absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center;
        }

        .loading-spinner {
            @apply animate-spin rounded-full h-8 w-8 md:h-12 md:w-12 border-4 border-t-blue-500;
        }

        .dashboard-header {
            @apply sticky top-0 z-10 bg-white border-b border-gray-200;
        }

        .dashboard-controls {
            @apply flex flex-col md:flex-row gap-3 md:gap-4 items-stretch md:items-center;
        }

        .select-control {
            @apply w-full md:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200;
        }

        .btn-primary {
            @apply bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center justify-center;
        }

        .stats-grid {
            @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6;
        }

        .chart-grid {
            @apply grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6;
        }

        .insights-section {
            @apply bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-100;
        }

        .dropdown-menu {
            @apply absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5;
        }

        .dropdown-item {
            @apply block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50;
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="dashboard-header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h2 class="text-xl font-semibold text-gray-800">
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
                            <button @click="open = !open" class="btn-primary">
                                Exportar
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="dropdown-menu">
                                <a href="{{ route('analytics.export.pdf', $business->id) }}" class="dropdown-item">
                                    Exportar PDF
                                </a>
                                <a href="{{ route('analytics.export.excel', $business->id) }}" class="dropdown-item">
                                    Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="metric-card">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Visualizações Totais</h3>
                    <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['total_views']) }}</span>
                        <span class="trend-indicator trend-up ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            12.5%
                        </span>
                    </div>
                </div>

                <div class="metric-card">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Cliques</h3>
                    <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['total_clicks']) }}</span>
                        <span class="trend-indicator trend-up ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            8.3%
                        </span>
                    </div>
                </div>

                <div class="metric-card">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Taxa de Conversão</h3>
                    <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['conversion_rate'], 2) }}%</span>
                        <span class="trend-indicator trend-up ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            2.1%
                        </span>
                    </div>
                </div>

                <div class="metric-card">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Tempo Médio</h3>
                    <div class="flex items-baseline">
                    <span class="text-2xl font-semibold text-gray-900">{{ $metrics['response_time'] }}</span>
                        <span class="trend-indicator trend-down ml-2">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                            </svg>
                            1.2%
                        </span>
                    </div>
                </div>
            </div>
                        <!-- Charts Grid -->
                        <div class="chart-grid">
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

            <!-- Insights Section -->
            <div class="insights-section">
                <h3 class="text-lg font-semibold mb-4">Insights</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($insights as $insight)
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">{{ $insight }}</p>
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
        // Chart.js Configuration
        Chart.defaults.font.family = 'Inter var, sans-serif';
        Chart.defaults.color = '#6B7280';
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;

        // Performance Chart
const performanceChart = new Chart(
    document.getElementById('performanceChart').getContext('2d'),
    {
        type: 'line',
        data: {
            labels: {!! json_encode($analyticsData['dates']) !!},
            datasets: [
                {
                    label: 'Visualizações',
                    data: {!! json_encode($analyticsData['views']) !!},
                    borderColor: '#4285F4',
                    backgroundColor: '#4285F4',
                    tension: 0.4
                },
                {
                    label: 'Cliques',
                    data: {!! json_encode($analyticsData['clicks']) !!},
                    borderColor: '#0F9D58',
                    backgroundColor: '#0F9D58',
                    tension: 0.4
                }
            ]
        }
    }
);

        // Engagement Chart
const engagementChart = new Chart(
    document.getElementById('engagementChart').getContext('2d'),
    {
        type: 'bar',
        data: {
            labels: {!! json_encode($analyticsData['dates']) !!},
            datasets: [
                {
                    label: 'Taxa de Conversão',
                    data: {!! json_encode($analyticsData['conversionRates']) !!},
                    backgroundColor: '#DB4437'
                }
            ]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    }
);

        // Event Handlers
        document.getElementById('period-selector').addEventListener('change', function(e) {
            updateDashboardData(e.target.value);
        });

        document.getElementById('business-selector').addEventListener('change', function(e) {
            window.location.href = `/analytics/dashboard/${e.target.value}`;
        });

        // Update Dashboard Data
        async function updateDashboardData(period) {
            const businessId = document.getElementById('business-selector').value;
            
            showLoaders();
            
            try {
                const response = await fetch(`/analytics/data/${businessId}?period=${period}`);
                const data = await response.json();
                
                updateCharts(data);
                updateMetrics(data.metrics);
                updateInsights(data.insights);
                
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
                showError('Erro ao atualizar dados do dashboard');
            } finally {
                hideLoaders();
            }
        }

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

        function updateCharts(data) {
            // Update Performance Chart
            performanceChart.data.labels = data.performance.labels;
            performanceChart.data.datasets[0].data = data.performance.views;
            performanceChart.data.datasets[1].data = data.performance.clicks;
            performanceChart.update();

            // Update Engagement Chart
            engagementChart.data.labels = data.engagement.labels;
            engagementChart.data.datasets[0].data = data.engagement.rates;
            engagementChart.update();
        }

        function updateMetrics(metrics) {
            // Update metrics display
            Object.keys(metrics).forEach(key => {
                const element = document.getElementById(`metric-${key}`);
                if (element) {
                    element.textContent = metrics[key];
                }
            });
        }

        function updateInsights(insights) {
            const container = document.querySelector('.insights-section .grid');
            container.innerHTML = insights.map(insight => `
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">${insight}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function showError(message) {
            // Implement error notification
            alert(message);
        }
    </script>
    @endpush
</x-app-layout>