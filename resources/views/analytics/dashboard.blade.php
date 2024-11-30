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
                Analytics - {{ $selectedBusiness->name }}
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
                        <option value="{{ $business->id }}" {{ $business->id == $selectedBusiness->id ? 'selected' : '' }}>
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
                            <a href="{{ route('analytics.export.pdf', $selectedBusiness->id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Exportar PDF
                            </a>
                            <a href="{{ route('analytics.export.excel', $selectedBusiness->id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Exportar Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Cards de Métricas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <x-metric-card
                    title="Visualizações"
                    :value="number_format(end($views))"
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
                    :value="number_format(end($clicks))"
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
                    title="Chamadas"
                    :value="number_format(end($calls))"
                    :growth="$growth['calls'] ?? null"
                    color="purple"
                >
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </x-slot>
                </x-metric-card>

                <x-metric-card
                    title="Total no Período"
                    :value="number_format(array_sum($views))"
                    color="orange"
                >
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </x-slot>
                </x-metric-card>
            </div>
            <!-- Gráfico Principal -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div id="main-chart" class="h-80"></div>
            </div>

            <!-- Gráficos Secundários -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Dispositivos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Dispositivos</h3>
                    <div id="devices-chart" class="h-64"></div>
                </div>

                <!-- Localização -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Localização</h3>
                    <div id="locations-chart" class="h-64"></div>
                </div>

                <!-- Palavras-chave -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Palavras-chave</h3>
                    <div id="keywords-chart" class="h-64"></div>
                </div>
            </div>

            <!-- Insights -->
            @if(!empty($insights))
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Insights</h3>
                <div class="space-y-4" id="insights-container">
                    @foreach($insights as $insight)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-900 dark:text-gray-100">{{ $insight }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Configurações dos gráficos
        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#D1D5DB' : '#374151';
        const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

        // Gráfico Principal
        const mainChartOptions = {
            series: [{
                name: 'Visualizações',
                data: @json($views)
            }, {
                name: 'Cliques',
                data: @json($clicks)
            }, {
                name: 'Chamadas',
                data: @json($calls)
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                },
                fontFamily: 'Inter, sans-serif',
                foreColor: textColor
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                categories: @json($dates),
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            tooltip: {
                theme: isDarkMode ? 'dark' : 'light'
            },
            grid: {
                borderColor: gridColor
            },
            legend: {
                labels: {
                    colors: textColor
                }
            },
            colors: ['#60A5FA', '#34D399', '#A78BFA']
        };

        const mainChart = new ApexCharts(document.querySelector("#main-chart"), mainChartOptions);
        mainChart.render();
        // Gráfico de Dispositivos
        const devicesChartOptions = {
            series: Object.values(@json($devices)),
            labels: Object.keys(@json($devices)),
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: 'Inter, sans-serif',
                foreColor: textColor
            },
            colors: ['#60A5FA', '#34D399', '#A78BFA'],
            legend: {
                position: 'bottom',
                labels: {
                    colors: textColor
                }
            },
            dataLabels: {
                formatter: function(val) {
                    return Math.round(val) + '%'
                }
            },
            theme: {
                mode: isDarkMode ? 'dark' : 'light'
            }
        };

        const devicesChart = new ApexCharts(document.querySelector("#devices-chart"), devicesChartOptions);
        devicesChart.render();

        // Gráfico de Localização
        const locationsChartOptions = {
            series: Object.values(@json($locations)),
            labels: Object.keys(@json($locations)),
            chart: {
                type: 'pie',
                height: 250,
                fontFamily: 'Inter, sans-serif',
                foreColor: textColor
            },
            colors: ['#F472B6', '#60A5FA', '#34D399'],
            legend: {
                position: 'bottom',
                labels: {
                    colors: textColor
                }
            },
            dataLabels: {
                formatter: function(val) {
                    return Math.round(val) + '%'
                }
            },
            theme: {
                mode: isDarkMode ? 'dark' : 'light'
            }
        };

        const locationsChart = new ApexCharts(document.querySelector("#locations-chart"), locationsChartOptions);
        locationsChart.render();

        // Gráfico de Palavras-chave
        const keywordsChartOptions = {
            series: [{
                data: Object.values(@json($keywords))
            }],
            chart: {
                type: 'bar',
                height: 250,
                fontFamily: 'Inter, sans-serif',
                foreColor: textColor,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    distributed: true,
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    colors: [textColor]
                }
            },
            xaxis: {
                categories: Object.keys(@json($keywords)),
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            grid: {
                borderColor: gridColor
            },
            theme: {
                mode: isDarkMode ? 'dark' : 'light'
            },
            colors: ['#60A5FA', '#34D399', '#A78BFA', '#F472B6']
        };

        const keywordsChart = new ApexCharts(document.querySelector("#keywords-chart"), keywordsChartOptions);
        keywordsChart.render();

        // Event Listeners
        document.getElementById('business-selector').addEventListener('change', function() {
            window.location.href = `/analytics/${this.value}`;
        });

        document.getElementById('period-selector').addEventListener('change', function() {
            if (this.value === 'custom') {
                // Abre o modal de período personalizado
                document.querySelector('[x-data="{ open: false }"]').__x.$data.open = true;
            } else {
                updateData(this.value);
            }
        });

        // Função para atualizar os dados
        function updateData(period) {
            fetch(`/analytics/data/${document.getElementById('business-selector').value}?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    // Atualiza o gráfico principal
                    mainChart.updateSeries([{
                        name: 'Visualizações',
                        data: data.views
                    }, {
                        name: 'Cliques',
                        data: data.clicks
                    }, {
                        name: 'Chamadas',
                        data: data.calls
                    }]);

                    // Atualiza gráfico de dispositivos
                    devicesChart.updateSeries(Object.values(data.devices));
                    devicesChart.updateOptions({
                        labels: Object.keys(data.devices)
                    });

                    // Atualiza gráfico de localização
                    locationsChart.updateSeries(Object.values(data.locations));
                    locationsChart.updateOptions({
                        labels: Object.keys(data.locations)
                    });

                    // Atualiza gráfico de palavras-chave
                    keywordsChart.updateSeries([{
                        data: Object.values(data.keywords)
                    }]);
                    keywordsChart.updateOptions({
                        xaxis: {
                            categories: Object.keys(data.keywords)
                        }
                    });

                    // Atualiza os cards de métricas
                    updateMetricCards(data);

                    // Atualiza insights
                    updateInsights(data.insights);
                });
        }

        // Função para atualizar os cards de métricas
        function updateMetricCards(data) {
            document.querySelector('[data-metric="visualizacoes"]').textContent = 
                number_format(data.views[data.views.length - 1]);
            document.querySelector('[data-metric="cliques"]').textContent = 
                number_format(data.clicks[data.clicks.length - 1]);
            document.querySelector('[data-metric="chamadas"]').textContent = 
                number_format(data.calls[data.calls.length - 1]);
            document.querySelector('[data-metric="total-no-periodo"]').textContent = 
                number_format(data.views.reduce((a, b) => a + b, 0));

            // Atualiza crescimento se disponível
            if (data.growth) {
                updateGrowthIndicators(data.growth);
            }
        }

        // Função para atualizar indicadores de crescimento
        function updateGrowthIndicators(growth) {
            const metrics = ['visualizacoes', 'cliques', 'chamadas'];
            metrics.forEach(metric => {
                const element = document.querySelector(`[data-growth="${metric}"]`);
                if (element && growth[metric] !== undefined) {
                    const value = growth[metric];
                    element.textContent = `${value >= 0 ? '+' : ''}${value}%`;
                    element.className = `mt-1 text-sm ${
                        value >= 0 
                            ? 'text-green-600 dark:text-green-400' 
                            : 'text-red-600 dark:text-red-400'
                    }`;
                }
            });
        }

        // Função para atualizar insights
        function updateInsights(insights) {
            const container = document.querySelector('#insights-container');
            if (container && insights) {
                container.innerHTML = insights.map(insight => `
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-900 dark:text-gray-100">${insight}</p>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Função auxiliar para formatação de números
        function number_format(number) {
            return new Intl.NumberFormat('pt-BR').format(number);
        }

        // Observer para modo escuro
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const isDark = document.documentElement.classList.contains('dark');
                    updateChartsTheme(isDark);
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        // Função para atualizar tema dos gráficos
        function updateChartsTheme(isDark) {
            const textColor = isDark ? '#D1D5DB' : '#374151';
            const gridColor = isDark ? '#374151' : '#E5E7EB';

            const commonOptions = {
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                chart: {
                    foreColor: textColor
                },
                grid: {
                    borderColor: gridColor
                }
            };

            [mainChart, devicesChart, locationsChart, keywordsChart].forEach(chart => {
                chart.updateOptions(commonOptions);
            });
        }
    </script>
    @endpush
</x-app-layout>