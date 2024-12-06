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
            @apply bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow;
        }

        .chart-container {
            @apply bg-white rounded-lg shadow-sm p-6;
        }

        .trend-indicator {
            @apply inline-flex items-center px-2 py-1 rounded-full text-sm;
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
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-xl font-google-sans text-gray-800">
                Analytics - {{ $business->name }}
            </h2>
            
            <div class="flex items-center space-x-4">
                <!-- Seletor de Período -->
                <select id="period-selector" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30" selected>Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                    <option value="custom">Período personalizado</option>
                </select>

                <!-- Seletor de Negócio -->
                <select id="business-selector" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
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
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <a href="{{ route('analytics.export.pdf', $business->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Exportar PDF
                            </a>
                            <a href="{{ route('analytics.export.excel', $business->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
                    <h3 class="text-lg font-google-sans text-gray-600">Visualizações</h3>
                    <p class="text-3xl font-google-sans mt-2">{{ number_format(array_sum($analyticsData['views'])) }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['views'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['views'] }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Cliques -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600">Cliques</h3>
                    <p class="text-3xl font-google-sans mt-2">{{ number_format(array_sum($analyticsData['clicks'])) }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['clicks'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['clicks'] }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Ligações -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600">Ligações</h3>
                    <p class="text-3xl font-google-sans mt-2">{{ number_format($metrics['calls']) }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['calls'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['calls'] }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Visitas ao Local -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600">Visitas ao Local</h3>
                    <p class="text-3xl font-google-sans mt-2">{{ number_format($metrics['visits']) }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['visits'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['visits'] }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Taxa de Conversão -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600">Taxa de Conversão</h3>
                    <p class="text-3xl font-google-sans mt-2">{{ number_format($metrics['conversion_rate'], 1) }}%</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['conversion'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['conversion'] }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Avaliação Média -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600">Avaliação Média</h3>
                    <p class="text-3xl font-google-sans mt-2">{{ number_format($metrics['rating'], 1) }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['rating'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['rating'] }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Tempo Médio de Resposta -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600">Tempo de Resposta</h3>
                    <p class="text-3xl font-google-sans mt-2">{{ $metrics['response_time'] }}</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['response_time'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['response_time'] }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs. período anterior</span>
                    </div>
                </div>

                <!-- Taxa de Engajamento -->
                <div class="metric-card">
                    <h3 class="text-lg font-google-sans text-gray-600">Taxa de Engajamento</h3>
                    <p class="text-3xl font-google-sans mt-2">{{ number_format($metrics['engagement_rate'], 1) }}%</p>
                    <div class="mt-2">
                        <span class="trend-indicator {{ $trends['engagement'] >= 0 ? 'trend-up' : 'trend-down' }}">
                            {{ $trends['engagement'] }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs. período anterior</span>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Gráfico de Performance -->
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Performance ao Longo do Tempo</h3>
                    <canvas id="performanceChart" height="300"></canvas>
                </div>

                <!-- Gráfico de Dispositivos -->
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Distribuição por Dispositivos</h3>
                    <canvas id="devicesChart" height="300"></canvas>
                </div>

                <!-- Gráfico de Origem do Tráfego -->
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Origem do Tráfego</h3>
                    <canvas id="trafficSourceChart" height="300"></canvas>
                </div>

                <!-- Mapa de Calor de Horários -->
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans text-gray-800 mb-4">Horários de Maior Movimento</h3>
                    <canvas id="heatmapChart" height="300"></canvas>
                </div>
            </div>

            <!-- Tabela de Dados Detalhados -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h3 class="text-lg font-google-sans text-gray-800 mb-4">Dados Detalhados</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Visualizações
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cliques
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ligações
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Visitas
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Conversão
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($dailyData as $data)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $data['date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($data['views']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($data['clicks']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($data['calls']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($data['visits']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($data['conversion'], 1) }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configuração dos gráficos
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['dates']) !!},
                datasets: [
                    {
                        label: 'Visualizações',
                        data: {!! json_encode($chartData['views']) !!},
                        borderColor: '#4285F4',
                        tension: 0.4
                    },
                    {
                        label: 'Cliques',
                        data: {!! json_encode($chartData['clicks']) !!},
                        borderColor: '#0F9D58',
                        tension: 0.4
                    },
                    {
                        label: 'Ligações',
                        data: {!! json_encode($chartData['calls']) !!},
                        borderColor: '#DB4437',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
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
        });

        // Gráfico de Dispositivos
        const devicesCtx = document.getElementById('devicesChart').getContext('2d');
        new Chart(devicesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    data: [
                        {{ $metrics['devices']['desktop'] }},
                        {{ $metrics['devices']['mobile'] }},
                        {{ $metrics['devices']['tablet'] }}
                    ],
                    backgroundColor: ['#4285F4', '#0F9D58', '#F4B400']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de Origem do Tráfego
        const trafficCtx = document.getElementById('trafficSourceChart').getContext('2d');
        new Chart(trafficCtx, {
            type: 'pie',
            data: {
                labels: ['Pesquisa', 'Maps', 'Direto', 'Referência'],
                datasets: [{
                    data: [
                        {{ $metrics['traffic']['search'] }},
                        {{ $metrics['traffic']['maps'] }},
                        {{ $metrics['traffic']['direct'] }},
                        {{ $metrics['traffic']['referral'] }}
                    ],
                    backgroundColor: ['#4285F4', '#0F9D58', '#F4B400', '#DB4437']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Event Listeners
        document.getElementById('period-selector').addEventListener('change', function(e) {
            window.location.href = `/analytics/${business.id}?period=${e.target.value}`;
        });

        document.getElementById('business-selector').addEventListener('change', function(e) {
            window.location.href = `/analytics/${e.target.value}`;
        });
    </script>
    @endpush
</x-app-layout>