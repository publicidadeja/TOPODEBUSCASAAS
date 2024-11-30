<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Performance - {{ $selectedBusiness->name }}
            </h2>
            <select id="business-selector" class="rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                @foreach($businesses as $business)
                    <option value="{{ $business->id }}" {{ $business->id == $selectedBusiness->id ? 'selected' : '' }}>
                        {{ $business->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Seletor de Período -->
            <div class="mb-6">
                <x-analytics.components.period-selector :selected="30" />
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <x-analytics.components.metric-card
                    title="Taxa de Cliques"
                    value="5.2%"
                    growth="0.8"
                    color="blue"
                />

                <x-analytics.components.metric-card
                    title="Taxa de Conversão"
                    value="2.8%"
                    growth="-0.3"
                    color="green"
                />

                <x-analytics.components.metric-card
                    title="Tempo de Resposta"
                    value="2h 15min"
                    growth="-15"
                    color="purple"
                />

                <x-analytics.components.metric-card
                    title="Satisfação"
                    value="4.5/5"
                    growth="0.2"
                    color="yellow"
                />
            </div>

            <!-- Gráficos de Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Gráfico de Conversões -->
                <x-analytics.components.chart-card
                    title="Taxa de Conversão"
                    chartId="conversion-chart"
                    description="Conversões diárias nos últimos 30 dias"
                >
                    <!-- O conteúdo do gráfico será renderizado via JavaScript -->
                </x-analytics.components.chart-card>

                <!-- Gráfico de Tempo de Resposta -->
                <x-analytics.components.chart-card
                    title="Tempo de Resposta"
                    chartId="response-time-chart"
                    description="Tempo médio de resposta por dia"
                >
                    <!-- O conteúdo do gráfico será renderizado via JavaScript -->
                </x-analytics.components.chart-card>
            </div>

            <!-- Tabela de Performance Detalhada -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Performance Detalhada
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Data
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Visualizações
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Cliques
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    CTR
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Conversões
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Taxa de Conversão
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Exemplo de linha -->
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        2024-01-15
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        1,234
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        85
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        6.89%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        12
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        14.12%
                                    </td>
                                </tr>
                                <!-- Adicione mais linhas conforme necessário -->
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
        // Configuração dos gráficos
        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#D1D5DB' : '#374151';
        const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

        // Gráfico de Conversões
        const conversionChartOptions = {
            series: [{
                name: 'Taxa de Conversão',
                data: [2.1, 2.8, 2.3, 3.1, 2.6, 2.9, 2.5, 2.7, 2.4, 2.8, 3.2, 2.9, 2.7, 2.5, 2.8]
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: false
                },
                foreColor: textColor
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                type: 'datetime',
                categories: Array.from({length: 15}, (_, i) => {
                    const d = new Date();
                    d.setDate(d.getDate() - (14 - i));
                    return d.toISOString().slice(0, 10);
                }),
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return value.toFixed(1) + '%';
                    },
                    style: {
                        colors: textColor
                    }
                }
            },
            grid: {
                borderColor: gridColor
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value.toFixed(2) + '%';
                    }
                }
            }
        };

        const conversionChart = new ApexCharts(document.querySelector("#conversion-chart"), conversionChartOptions);
        conversionChart.render();

        // Gráfico de Tempo de Resposta
        const responseTimeChartOptions = {
            series: [{
                name: 'Tempo de Resposta',
                data: [120, 95, 150, 85, 130, 110, 140, 125, 95, 105, 135, 115, 90, 125, 145]
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                },
                foreColor: textColor
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            xaxis: {
                type: 'datetime',
                categories: Array.from({length: 15}, (_, i) => {
                    const d = new Date();
                    d.setDate(d.getDate() - (14 - i));
                    return d.toISOString().slice(0, 10);
                }),
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return Math.floor(value / 60) + 'h ' + (value % 60) + 'm';
                    },
                    style: {
                        colors: textColor
                    }
                }
            },
            grid: {
                borderColor: gridColor
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return Math.floor(value / 60) + 'h ' + (value % 60) + 'm';
                    }
                }
            }
        };

        const responseTimeChart = new ApexCharts(document.querySelector("#response-time-chart"), responseTimeChartOptions);
        responseTimeChart.render();

        // Event Listeners
        document.getElementById('business-selector').addEventListener('change', function() {
            window.location.href = `/analytics/performance/${this.value}`;
        });

        // Dark mode observer
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

        function updateChartsTheme(isDark) {
            const textColor = isDark ? '#D1D5DB' : '#374151';
            const gridColor = isDark ? '#374151' : '#E5E7EB';

            [conversionChart, responseTimeChart].forEach(chart => {
                chart.updateOptions({
                    theme: {
                        mode: isDark ? 'dark' : 'light'
                    },
                    chart: {
                        foreColor: textColor
                    },
                    grid: {
                        borderColor: gridColor
                    }
                });
            });
        }
    </script>
    @endpush
</x-app-layout>