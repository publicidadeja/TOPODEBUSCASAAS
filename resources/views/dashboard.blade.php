<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <x-business-selector :businesses="$businesses" :selected="$selectedBusiness" route="dashboard" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Performance Overview -->
            <div class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-semibold text-gray-600 dark:text-gray-400">Visualizações</h4>
                        <p class="text-3xl font-bold mt-2 dark:text-gray-100">{{ number_format($analytics['views']) }}</p>
                        <div class="mt-2">
                            <span class="text-sm {{ $analytics['trends']['views'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $analytics['trends']['views'] }}%
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">vs. período anterior</span>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-semibold text-gray-600 dark:text-gray-400">Cliques</h4>
                        <p class="text-3xl font-bold mt-2 dark:text-gray-100">{{ number_format($analytics['clicks']) }}</p>
                        <div class="mt-2">
                            <span class="text-sm {{ $analytics['trends']['clicks'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $analytics['trends']['clicks'] }}%
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">vs. período anterior</span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-semibold text-gray-600 dark:text-gray-400">Chamadas</h4>
                        <p class="text-3xl font-bold mt-2 dark:text-gray-100">{{ number_format($analytics['calls']) }}</p>
                        <div class="mt-2">
                            <span class="text-sm {{ $analytics['trends']['calls'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $analytics['trends']['calls'] }}%
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">vs. período anterior</span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-semibold text-gray-600 dark:text-gray-400">Taxa de Conversão</h4>
                        <p class="text-3xl font-bold mt-2 dark:text-gray-100">{{ $analytics['conversion_rate'] }}%</p>
                        <div class="mt-2">
                            <span class="text-sm {{ $analytics['trends']['conversion'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $analytics['trends']['conversion'] }}%
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">vs. período anterior</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-gray-300">Desempenho ao Longo do Tempo</h3>
                <div class="h-[300px]">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

           <!-- Insights e Alertas -->
<div class="mb-8">
    <h3 class="text-lg font-semibold mb-4 dark:text-gray-300">Insights e Alertas</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($suggestions as $suggestion)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    @if($suggestion['type'] === 'success')
                        <svg class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($suggestion['type'] === 'warning')
                        <svg class="h-6 w-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">{{ $suggestion['message'] }}</p>
                    @if(isset($suggestion['action']))
                        <a href="{{ $suggestion['action_url'] }}" class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400">
                            {{ $suggestion['action'] }}
                            <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

            <!-- Ações Rápidas -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 dark:text-gray-300">Ações Rápidas</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                   <a href="{{ route('analytics.index', ['business' => $selectedBusiness->id]) }}"
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 text-center">
                        <svg class="h-6 w-6 mx-auto mb-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Analytics Detalhado</span>
                    </a>

                    <a href="{{ route('analytics.competitors', ['business' => $selectedBusiness->id]) }}"
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 text-center">
                        <svg class="h-6 w-6 mx-auto mb-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Análise Competitiva</span>
                    </a>

                    <a href="{{ route('business.edit', ['business' => $selectedBusiness->id]) }}"
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 text-center">
                        <svg class="h-6 w-6 mx-auto mb-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Editar Informações</span>
                    </a>

                    <a href="#" onclick="event.preventDefault(); document.getElementById('export-form').submit();"
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 text-center">
                        <svg class="h-6 w-6 mx-auto mb-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Exportar Relatório</span>
                    </a>
                </div>
            </div>

            <!-- Dispositivos e Localização -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Distribuição de Dispositivos -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-gray-300">Dispositivos</h3>
        <div class="h-[300px]"> <!-- Altura fixa definida -->
            <canvas id="devicesChart"></canvas>
        </div>
    </div>

    <!-- Principais Localizações -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-gray-300">Principais Localizações</h3>
        <div class="h-[300px] overflow-y-auto"> <!-- Altura fixa com scroll -->
            <div class="space-y-4">
                @foreach($analytics['top_locations'] as $location => $percentage)
                <div class="flex items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-24">{{ $location }}</span>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $percentage }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

    <!-- Export Form -->
    <form id="export-form" action="{{ route('analytics.export.excel', ['business' => $selectedBusiness->id]) }}" method="GET" class="hidden">
        @csrf
    </form>

    @push('scripts')
    <script>
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($analytics['dates']) !!},
                datasets: [
                    {
                        label: 'Visualizações',
                        data: {!! json_encode($analytics['daily_views']) !!},
                        borderColor: '#3B82F6',
                        tension: 0.1
                    },
                    {
                        label: 'Cliques',
                        data: {!! json_encode($analytics['daily_clicks']) !!},
                        borderColor: '#10B981',
                        tension: 0.1
                    },
                    {
                        label: 'Chamadas',
                        data: {!! json_encode($analytics['daily_calls']) !!},
                        borderColor: '#F59E0B',
                        tension: 0.1
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
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Devices Chart
const devicesCtx = document.getElementById('devicesChart').getContext('2d');
new Chart(devicesCtx, {
    type: 'doughnut',
    data: {
        labels: ['Desktop', 'Mobile', 'Tablet'],
        datasets: [{
            data: [
                {{ $analytics['devices']['desktop'] ?? 0 }},
                {{ $analytics['devices']['mobile'] ?? 0 }},
                {{ $analytics['devices']['tablet'] ?? 0 }}
            ],
            backgroundColor: [
                '#3B82F6',
                '#10B981',
                '#F59E0B'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true, // Alterado para true
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
    </script>
    @endpush
</x-app-layout>