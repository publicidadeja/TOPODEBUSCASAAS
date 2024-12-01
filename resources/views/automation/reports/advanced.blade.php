// resources/views/automation/reports/advanced.blade.php

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Relatório Avançado') }}
            </h2>
            <div class="flex space-x-4">
                <!-- Filtros -->
                <div class="flex items-center space-x-2">
                    <x-input type="date" name="start_date" class="text-sm" />
                    <span class="text-gray-500">até</span>
                    <x-input type="date" name="end_date" class="text-sm" />
                    <x-button type="button" onclick="applyFilters()">
                        Filtrar
                    </x-button>
                </div>
                
                <!-- Exportar -->
                <div class="relative" x-data="{ open: false }">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-200">
                                <span>Exportar</span>
                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link href="#" onclick="exportReport('pdf')">
                                Exportar PDF
                            </x-dropdown-link>
                            <x-dropdown-link href="#" onclick="exportReport('excel')">
                                Exportar Excel
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Insights -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Insights</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($insights as $insight)
                            <div class="p-4 rounded-lg {{ $insight['type'] === 'success' ? 'bg-green-50 dark:bg-green-900' : 'bg-yellow-50 dark:bg-yellow-900' }}">
                                <p class="text-sm {{ $insight['type'] === 'success' ? 'text-green-700 dark:text-green-100' : 'text-yellow-700 dark:text-yellow-100' }}">
                                    {{ $insight['message'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Métricas Principais -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Performance -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Performance</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Visualizações</p>
                                <p class="text-2xl font-bold">{{ number_format($metrics['performance']['views']['total']) }}</p>
                                <p class="text-sm {{ $metrics['performance']['views']['growth'] > 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $metrics['performance']['views']['growth'] }}%
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Cliques</p>
                                <p class="text-2xl font-bold">{{ number_format($metrics['performance']['clicks']['total']) }}</p>
                                <p class="text-sm {{ $metrics['performance']['clicks']['growth'] > 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $metrics['performance']['clicks']['growth'] }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Engajamento -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Engajamento</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Taxa de Interação</p>
                                <p class="text-2xl font-bold">{{ $metrics['engagement']['interaction_rate'] }}%</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tempo Médio de Resposta</p>
                                <p class="text-2xl font-bold">{{ round($metrics['engagement']['responses']['average_time']/60, 1) }}h</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Automação -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Automação</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Eficiência</p>
                                <p class="text-2xl font-bold">{{ $metrics['automation']['efficiency'] }}%</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Posts Automatizados</p>
                                <p class="text-2xl font-bold">{{ $metrics['automation']['posts']['total'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gráfico de Performance -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Performance ao Longo do Tempo</h3>
                        <div id="performance-chart" class="h-80"></div>
                    </div>
                </div>

                <!-- Gráfico de Engajamento -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Engajamento ao Longo do Tempo</h3>
                        <div id="engagement-chart" class="h-80"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Configuração dos gráficos
        const performanceChart = new ApexCharts(document.querySelector("#performance-chart"), {
            // Configurações do gráfico de performance
        });
        performanceChart.render();

        const engagementChart = new ApexCharts(document.querySelector("#engagement-chart"), {
            // Configurações do gráfico de engajamento
        });
        engagementChart.render();

        // Funções de exportação e filtros
        function exportReport(format) {
            const params = new URLSearchParams(window.location.search);
            params.append('format', format);
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }

        function applyFilters() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            
            if (startDate && endDate) {
                const params = new URLSearchParams(window.location.search);
                params.set('start_date', startDate);
                params.set('end_date', endDate);
                window.location.href = `${window.location.pathname}?${params.toString()}`;
            }
        }
    </script>
    @endpush
</x-app-layout>