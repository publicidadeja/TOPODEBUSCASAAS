<x-app-layout>
    @push('styles')
    <style>
        .apexcharts-legend-text {
            font-family: inherit !important;
        }
        
        .apexcharts-tooltip {
            font-family: inherit !important;
        }

        .dark .apexcharts-tooltip {
            background: rgba(0, 0, 0, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .dark .apexcharts-tooltip-title {
            background: rgba(0, 0, 0, 0.7) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
    Analytics - {{ $business->name }}
</h2>
            <div class="flex items-center space-x-4">
                <!-- Seletor de Período -->
                <select id="period-selector" class="rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30" selected>Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                    <option value="custom">Período personalizado</option>
                </select>

                <!-- Seletor de Negócio -->
                <select id="business-selector" class="rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @foreach($businesses as $business)
                    <option value="{{ $business->id }}" {{ $business->id == $currentBusiness->id ? 'selected' : '' }}>
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Botão Exportar -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        Exportar
                        <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
    <a href="{{ route('analytics.export.pdf', $business->id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
        Exportar PDF
    </a>
    <a href="{{ route('analytics.export.excel', $business->id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
        Exportar Excel
    </a>
</div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @if(isset($aiAnalysis))
    <!-- Your AI analysis content -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Análise Inteligente</h3>
        <div class="prose dark:prose-invert">
            {!! nl2br(e($aiAnalysis['analysis'])) !!}
        </div>
        <div class="text-sm text-gray-500 mt-4">
            Última atualização: {{ $aiAnalysis['timestamp']->diffForHumans() }}
        </div>
    </div>
    @endif

    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Cards de Métricas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-metric-card
    title="Visualizações"
    :value="number_format(!empty($analytics['views']) ? end($analytics['views']) : 0)"
    :growth="$growth['views'] ?? null"
    color="blue"
>
    <x-slot name="icon">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
    </x-slot>
</x-metric-card>

<x-metric-card
    title="Cliques"
    :value="number_format(!empty($analytics['clicks']) ? end($analytics['clicks']) : 0)"
    :growth="$growth['clicks'] ?? null"
    color="green"
>
    <x-slot name="icon">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
        </svg>
    </x-slot>
</x-metric-card>

<x-metric-card
    title="Taxa de Conversão"
    :value="number_format($analytics['currentConversion'], 1) . '%'"
    :growth="$growth['conversion'] ?? null"
    color="purple"
>
    <x-slot name="icon">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2"/>
        </svg>
    </x-slot>
</x-metric-card>

<x-metric-card
    title="Avaliação Média"
    :value="number_format($analytics['averageRating'], 1)"
    :growth="$growth['rating'] ?? null"
    color="yellow"
>
    <x-slot name="icon">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3."/>
        </svg>
    </x-slot>
</x-metric-card>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Gráfico de Visualizações e Cliques -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Visualizações e Cliques</h3>
                    <div id="views-clicks-chart" class="h-80"></div>
                </div>

                <!-- Gráfico de Taxa de Conversão -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Taxa de Conversão</h3>
                    <div id="conversion-chart" class="h-80"></div>
                </div>
            </div>

            <!-- Insights e Recomendações -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
    <div class="p-6">
        <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Insights e Recomendações</h3>
        <div class="space-y-4">
            @foreach($analytics['insights'] as $insight)
                <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex-shrink-0">
                        @if($insight->type === 'success')
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif($insight->type === 'warning')
                            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $insight->title }}</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $insight->description }}</p>
                                </div>
                                @if($insight->action)
                                    <div class="flex-shrink-0">
                                        <button class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-700 transition duration-150 ease-in-out">
                                            {{ $insight->action }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tabela de Ações -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">Histórico de Ações</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs leading-4 font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs leading-4 font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ação</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs leading-4 font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Resultado</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs leading-4 font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($actions as $action)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900 dark:text-gray-100">
                                            {{ $action->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900 dark:text-gray-100">
                                            {{ $action->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900 dark:text-gray-100">
                                            {{ $action->result }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $action->status === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $action->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $action->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($action->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Configuração do gráfico de Visualizações e Cliques
        const viewsClicksChart = new ApexCharts(document.querySelector("#views-clicks-chart"), {
            series: [{
                name: 'Visualizações',
                data: @json($views)
            }, {
                name: 'Cliques',
                data: @json($clicks)
            }],
            chart: {
                type: 'line',
                height: 320,
                toolbar: {
                    show: false
                },
                background: 'transparent'
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#3B82F6', '#10B981'],
            xaxis: {
                categories: @json($dates),
                labels: {
                    style: {
                        colors: '#6B7280'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#6B7280'
                    }
                }
            },
            legend: {
                labels: {
                    colors: '#6B7280'
                }
            },
            grid: {
                borderColor: '#E5E7EB'
            },
            theme: {
                mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
            }
        });
        viewsClicksChart.render();

        // Configuração do gráfico de Taxa de Conversão
        const conversionChart = new ApexCharts(document.querySelector("#conversion-chart"), {
            series: [{
                name: 'Taxa de Conversão',
                data: @json($conversionRates)
            }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: {
                    show: false
                },
                background: 'transparent'
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#8B5CF6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3
                }
            },
            xaxis: {
                categories: @json($dates),
                labels: {
                    style: {
                        colors: '#6B7280'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#6B7280'
                    },
                    formatter: function(value) {
                        return value.toFixed(1) + '%'
                    }
                }
            },
            legend: {
                labels: {
                    colors: '#6B7280'
                }
            },
            grid: {
                borderColor: '#E5E7EB'
            },
            theme: {
                mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
            }
        });
        conversionChart.render();

        // Atualização dos gráficos quando mudar o tema
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.addEventListener('dark-mode', function(e) {
                viewsClicksChart.updateOptions({
                    theme: {
                        mode: e.detail ? 'dark' : 'light'
                    }
                });
                conversionChart.updateOptions({
                    theme: {
                        mode: e.detail ? 'dark' : 'light'
                    }
                });
            });
        }

        // Manipuladores de eventos para os seletores
        document.getElementById('period-selector').addEventListener('change', function(e) {
            // Implementar lógica de mudança de período
        });

        document.getElementById('business-selector').addEventListener('change', function(e) {
            window.location.href = `/analytics/dashboard/${e.target.value}`;
        });
    </script>
    @endpush
</x-app-layout>