<x-app-layout>
    @push('styles')
    <style>
        /* Custom styles for Google-inspired design */
        :root {
            --google-blue: #4285F4;
            --google-red: #DB4437;
            --google-yellow: #F4B400;
            --google-green: #0F9D58;
            --surface-color: #FFFFFF;
            --border-color: #DADCE0;
            --text-primary: #202124;
            --text-secondary: #5F6368;
        }

        .metric-card {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            transition: box-shadow 0.2s ease;
        }

        .metric-card:hover {
            box-shadow: 0 1px 3px rgba(60,64,67,0.3);
        }

        .chart-container {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
        }

        .trend-indicator {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 16px;
            font-size: 0.875rem;
        }

        .trend-up {
            background-color: #E6F4EA;
            color: var(--google-green);
        }

        .trend-down {
            background-color: #FCE8E6;
            color: var(--google-red);
        }

        /* Responsive Grid System */
        .analytics-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        /* Material Design Input Styles */
        .material-input {
            position: relative;
            margin-bottom: 1rem;
        }

        .material-input input,
        .material-input select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            transition: border-color 0.2s ease;
        }

        .material-input input:focus,
        .material-input select:focus {
            border-color: var(--google-blue);
            outline: none;
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-xl font-google-sans text-gray-800">
                Analytics - {{ $business->name }}
            </h2>
            <div class="flex items-center space-x-4">
                <!-- Period Selector -->
                <div class="material-input">
                    <select id="period-selector" class="text-gray-700">
                        <option value="7">Últimos 7 dias</option>
                        <option value="30" selected>Últimos 30 dias</option>
                        <option value="90">Últimos 90 dias</option>
                        <option value="custom">Período personalizado</option>
                    </select>
                </div>

                <!-- Business Selector -->
                <div class="material-input">
                    <select id="business-selector" class="text-gray-700">
                        @foreach($businesses as $b)
                            <option value="{{ $b->id }}" {{ $b->id == $business->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Export Button -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center">
                        Exportar
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
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
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- AI Analysis Section -->
            @if(isset($aiAnalysis))
                <div class="mb-6 p-6 bg-white rounded-lg border border-gray-200">
                    <h3 class="text-lg font-google-sans mb-4 text-gray-800">Análise Inteligente</h3>
                    <div class="prose">
                        @if(is_array($aiAnalysis['analysis']))
                            @if(isset($aiAnalysis['analysis']['content']))
                                {!! nl2br(e($aiAnalysis['analysis']['content'])) !!}
                            @else
                                {!! nl2br(e(json_encode($aiAnalysis['analysis'], JSON_PRETTY_PRINT))) !!}
                            @endif
                        @else
                            {!! nl2br(e($aiAnalysis['analysis'])) !!}
                        @endif
                    </div>
                    <div class="text-sm text-gray-500 mt-4">
                        Última atualização: {{ isset($aiAnalysis['timestamp']) ? 
                            \Carbon\Carbon::parse($aiAnalysis['timestamp'])->diffForHumans() : 
                            'Não disponível' }}
                    </div>
                </div>
            @endif

            <!-- Metrics Grid -->
            <div class="analytics-grid mb-6">
                <x-metric-card
                    title="Visualizações"
                    :value="number_format(!empty($analytics['views']) ? end($analytics['views']) : 0)"
                    :growth="$growth['views'] ?? null"
                    color="blue"
                />

                <x-metric-card
                    title="Cliques"
                    :value="number_format(!empty($analytics['clicks']) ? end($analytics['clicks']) : 0)"
                    :growth="$growth['clicks'] ?? null"
                    color="green"
                />

                <x-metric-card
                    title="Taxa de Conversão"
                    :value="number_format($analytics['currentConversion'], 1) . '%'"
                    :growth="$growth['conversion'] ?? null"
                    color="yellow"
                />

                <x-metric-card
                    title="Avaliação Média"
                    :value="number_format($analytics['averageRating'], 1)"
                    :growth="$growth['rating'] ?? null"
                    color="red"
                />
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="chart-container">
                    <h3 class="text-lg font-google-sans mb-4 text-gray-800">Visualizações e Cliques</h3>
                    <div id="views-clicks-chart" class="h-80"></div>
                </div>

                <div class="chart-container">
                    <h3 class="text-lg font-google-sans mb-4 text-gray-800">Taxa de Conversão</h3>
                    <div id="conversion-chart" class="h-80"></div>
                </div>
            </div>

            <!-- Actions Table -->
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-google-sans mb-4 text-gray-800">Histórico de Ações</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($actions as $action)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $action->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $action->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $action->result }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
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
        // Charts Configuration
        const viewsClicksChart = new ApexCharts(document.querySelector("#views-clicks-chart"), {
            series: [{
                name: 'Visualizações',
                data: @json($analytics['views'])
            }, {
                name: 'Cliques',
                data: @json($analytics['clicks'])
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
            colors: ['#4285F4', '#34A853'],
            xaxis: {
                categories: @json($analytics['dates']),
                labels: {
                    style: {
                        colors: '#5F6368'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#5F6368'
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            grid: {
                borderColor: '#DADCE0'
            }
        });
        viewsClicksChart.render();

        const conversionChart = new ApexCharts(document.querySelector("#conversion-chart"), {
            series: [{
                name: 'Taxa de Conversão',
                data: @json($analytics['conversionRates'])
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
            colors: ['#4285F4'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3
                }
            },
            xaxis: {
                categories: @json($analytics['dates']),
                labels: {
                    style: {
                        colors: '#5F6368'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#5F6368'
                    },
                    formatter: function(value) {
                        return value.toFixed(1) + '%'
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            grid: {
                borderColor: '#DADCE0'
            }
        });
        conversionChart.render();

        // Event Listeners
        document.getElementById('business-selector').addEventListener('change', function(e) {
            window.location.href = `/analytics/dashboard/${e.target.value}`;
        });

        document.getElementById('period-selector').addEventListener('change', function(e) {
            // Implement period change logic
        });
    </script>
    @endpush
</x-app-layout>