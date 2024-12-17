<x-app-layout>
<meta name="csrf-token" content="{{ csrf_token() }}">
    @push('styles')
    <style>

.keyword-card {
    transition: all 0.3s ease;
}

.keyword-card:hover {
    transform: translateY(-2px);
}

.competition-bar {
    transition: width 0.5s ease;
}
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
        <!-- Título com gradiente e ícone -->
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-semibold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                    Analytics
                </h2>
                <span class="text-sm text-gray-500">{{ $business->name }}</span>
            </div>
        </div>
        
        <!-- Controles -->
        <div class="flex items-center space-x-4">
            <!-- Seletor de Negócio -->
            <div class="relative">
                <select id="business-selector" 
                        class="pl-3 pr-10 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200">
                    @foreach($businesses as $b)
                        <option value="{{ $b->id }}" {{ $b->id == $business->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <!-- Seletor de Período -->
            <div class="relative">
                <select id="period-selector" 
                        class="pl-3 pr-10 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30" selected>Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <!-- Botão de Exportar com Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Exportar
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    
                    <a href="{{ route('analytics.export.pdf', $business->id) }}" 
                       class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                        <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Exportar PDF
                    </a>
                    
                    <a href="{{ route('analytics.export.excel', $business->id) }}" 
                       class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Exportar Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-slot>
    <div class="dashboard-container">
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Analytics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Views Metric -->
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600">Visualizações</h3>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-bold text-gray-900" id="metric-views">
                            {{ number_format($metrics['total_views']) }}
                        </span>
                        @if($metrics['trends']['views'] > 0)
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-50 text-green-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format($metrics['trends']['views'], 1) }}%
                            </div>
                        @else
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-50 text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format(abs($metrics['trends']['views']), 1) }}%
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Clicks Metric -->
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="p-2 bg-purple-50 rounded-lg">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600">Cliques</h3>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-bold text-gray-900" id="metric-clicks">
                            {{ number_format($metrics['total_clicks']) }}
                        </span>
                        @if($metrics['trends']['clicks'] > 0)
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-50 text-green-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format($metrics['trends']['clicks'], 1) }}%
                            </div>
                        @else
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-50 text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format(abs($metrics['trends']['clicks']), 1) }}%
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Calls Metric -->
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="p-2 bg-green-50 rounded-lg">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600">Ligações</h3>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-bold text-gray-900" id="metric-calls">
                            {{ number_format($metrics['total_calls']) }}
                        </span>
                        @if($metrics['trends']['calls'] > 0)
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-50 text-green-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format($metrics['trends']['calls'], 1) }}%
                            </div>
                        @else
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-50 text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format(abs($metrics['trends']['calls']), 1) }}%
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Conversion Rate Metric -->
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="p-2 bg-yellow-50 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600">Taxa de Conversão</h3>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-bold text-gray-900" id="metric-conversion">
                            {{ number_format($metrics['conversion_rate'], 2) }}%
                        </span>
                        @if($metrics['trends']['conversion'] > 0)
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-50 text-green-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format($metrics['trends']['conversion'], 1) }}%
                            </div>
                        @else
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-50 text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format(abs($metrics['trends']['conversion']), 1) }}%
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Response Time Metric -->
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="p-2 bg-red-50 rounded-lg">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-600">Tempo de Resposta</h3>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-bold text-gray-900" id="metric-response">
                            {{ $metrics['response_time'] }}
                        </span>
                        @if($metrics['trends']['response_time'] < 0)
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-50 text-green-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 8.414V15a1 1 0 11-2 0V8.414L6.707 11.121a1 1 0 01-1.414-1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format(abs($metrics['trends']['response_time']), 1) }}%
                            </div>
                        @else
                            <div class="flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-50 text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 11.586V5a1 1 0 012 0v6.586l2.293-2.293a1 1 0 111.414 1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ number_format($metrics['trends']['response_time'], 1) }}%
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>        

 <!-- Charts Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Performance Chart -->
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Performance</h3>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button class="p-2 hover:bg-gray-50 rounded-lg transition-colors duration-200" title="Download PDF">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </button>
                    <button class="p-2 hover:bg-gray-50 rounded-lg transition-colors duration-200" title="Atualizar">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="relative h-[400px]">
                <canvas id="performanceChart" class="w-full h-full"></canvas>
                
                <!-- Estado de Carregamento Aprimorado -->
                <div id="performanceChartLoader" class="absolute inset-0 bg-white bg-opacity-75 hidden">
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="relative">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <span class="mt-4 text-sm text-gray-600">Carregando dados...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Chart -->
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-purple-50 rounded-lg">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Engajamento</h3>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button class="p-2 hover:bg-gray-50 rounded-lg transition-colors duration-200" title="Download PDF">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </button>
                    <button class="p-2 hover:bg-gray-50 rounded-lg transition-colors duration-200" title="Atualizar">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="relative h-[400px]">
                <canvas id="engagementChart" class="w-full h-full"></canvas>
                
                <!-- Estado de Carregamento Aprimorado -->
                <div id="engagementChartLoader" class="absolute inset-0 bg-white bg-opacity-75 hidden">
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="relative">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <span class="mt-4 text-sm text-gray-600">Carregando dados...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



 <!-- Seção de Palavras-chave -->
<div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-indigo-50 rounded-lg">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Palavras-chave Populares</h3>
                <p class="text-sm text-gray-500">Palavras mais buscadas relacionadas ao seu negócio</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-4">
            <span id="last-update" class="text-sm text-gray-500"></span>
            <button onclick="refreshKeywords()" class="flex items-center space-x-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Atualizar</span>
            </button>
        </div>
    </div>
    
    <div id="keywords-container" class="relative">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($keywords as $term => $data)
            <div class="bg-gradient-to-br from-indigo-50/50 to-white p-6 rounded-xl border border-indigo-100/50 hover:shadow-md transition-all duration-300">
                <div class="flex flex-col">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-lg font-semibold text-gray-800">{{ $term }}</span>
                        <span class="px-3 py-1 text-sm bg-indigo-100 text-indigo-700 rounded-full">
                            {{ isset($data['volume']) ? number_format($data['volume']) : 'N/A' }} buscas
                        </span>
                    </div>
                    
                    @if(isset($data['trend']))
                    <div class="flex items-center mt-2">
                        @if($data['trend'] > 0)
                        <div class="flex items-center text-green-600 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <span>+{{ number_format($data['trend'], 1) }}% este mês</span>
                        </div>
                        @else
                        <div class="flex items-center text-red-600 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                            </svg>
                            <span>{{ number_format($data['trend'], 1) }}% este mês</span>
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    @if(isset($data['competition']))
                    <div class="mt-3">
                        <div class="text-sm text-gray-500 mb-1">Nível de competição</div>
                        <div class="h-2 bg-gray-100 rounded-full">
                            <div class="h-2 bg-indigo-500 rounded-full" style="width: {{ $data['competition'] }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Estado de carregamento -->
        <div id="keywords-loader" class="hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
                <span class="mt-4 text-sm text-gray-600">Atualizando palavras-chave...</span>
            </div>
        </div>
    </div>
</div>

<script>
function refreshKeywords() {
    const container = document.getElementById('keywords-container');
    const lastUpdate = document.getElementById('last-update');
    const businessId = '{{ $business->id }}';
    
    // Desabilita o botão de atualização
    const refreshButton = document.querySelector('button[onclick="refreshKeywords()"]');
    refreshButton.disabled = true;
    refreshButton.classList.add('opacity-50');
    
    // Mostra loading
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
            <span class="mt-4 text-sm text-gray-600">Buscando palavras-chave...</span>
        </div>
    `;

    // Faz a requisição para atualizar as palavras-chave
    fetch(`/analytics/refresh-keywords/${businessId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    })
    .then(data => {
        if (!data.keywords || Object.keys(data.keywords).length === 0) {
            throw new Error('Nenhuma palavra-chave encontrada');
        }

        // Atualiza o container com as novas palavras-chave
        container.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                ${Object.entries(data.keywords).map(([term, data]) => `
                    <div class="bg-gradient-to-br from-indigo-50/50 to-white p-4 rounded-xl border border-indigo-100/50 hover:shadow-sm transition-all duration-300">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-600 mb-1">${term}</span>
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-xl font-semibold text-gray-900">${Number(data.volume).toLocaleString()}</span>
                                    <span class="text-xs text-gray-500">buscas/mês</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: ${data.relevance}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">${data.relevance}% relevância</span>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        // Atualiza a data da última atualização
        lastUpdate.textContent = `Última atualização: ${new Date().toLocaleTimeString()}`;
        
        // Mostra notificação de sucesso
        showNotification('Palavras-chave atualizadas com sucesso!', 'success');
    })
    .catch(error => {
        console.error('Erro:', error);
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 px-4 text-center">
                <div class="p-3 bg-red-50 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-red-500 mb-4">${error.message}</p>
                <button onclick="refreshKeywords()" class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors">
                    Tentar novamente
                </button>
            </div>
        `;
        
        // Mostra notificação de erro
        showNotification('Erro ao atualizar palavras-chave', 'error');
    })
    .finally(() => {
        // Reabilita o botão de atualização
        refreshButton.disabled = false;
        refreshButton.classList.remove('opacity-50');
    });
}

// Função auxiliar para mostrar notificações
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } shadow-lg z-50 transform transition-all duration-300 translate-y-0`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('translate-y-full', 'opacity-0');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Atualiza as palavras-chave automaticamente a cada 30 minutos
setInterval(refreshKeywords, 30 * 60 * 1000);
</script>

<!-- Seção de Insights e Análises -->
<div class="mt-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                Insights e Análises
            </h3>
        </div>

        <button onclick="refreshInsights()" 
                id="refresh-insights-btn"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 shadow-sm">
            <svg id="refresh-icon" class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span id="refresh-text">Atualizar Insights</span>
        </button>
    </div>

    <!-- Loading State -->
    <div id="insights-loader" class="hidden">
        <div class="flex items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            <span class="ml-3 text-gray-600">Atualizando análises...</span>
        </div>
    </div>

    <!-- Content Container -->
    <div id="insights-content" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Performance Card -->
        @if(isset($aiAnalysis['performance']))
        <div class="group bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-blue-100 transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900">Performance</h4>
                        <span class="text-sm text-gray-500">Análise de desempenho</span>
                    </div>
                </div>
                <p class="text-gray-600 leading-relaxed">{{ $aiAnalysis['performance']['message'] }}</p>
                
                <div class="mt-4 pt-4 border-t border-gray-50">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Score geral</span>
                        <span class="font-medium text-blue-600">{{ $aiAnalysis['performance']['score'] ?? '85%' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Opportunities Card -->
        @if(isset($aiAnalysis['opportunities']))
        <div class="group bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-green-100 transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900">Oportunidades</h4>
                        <span class="text-sm text-gray-500">Potenciais melhorias</span>
                    </div>
                </div>
                <p class="text-gray-600 leading-relaxed">{{ $aiAnalysis['opportunities']['message'] }}</p>
                
                @if(isset($aiAnalysis['opportunities']['items']))
                <div class="mt-4 space-y-2">
                    @foreach($aiAnalysis['opportunities']['items'] as $item)
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                        </svg>
                        {{ $item }}
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Alerts Card -->
        @if(isset($aiAnalysis['alerts']))
        <div class="group bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-red-100 transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900">Alertas</h4>
                        <span class="text-sm text-gray-500">Pontos de atenção</span>
                    </div>
                </div>
                <p class="text-gray-600 leading-relaxed">{{ $aiAnalysis['alerts']['message'] }}</p>
                
                @if(isset($aiAnalysis['alerts']['urgency']))
                <div class="mt-4 pt-4 border-t border-gray-50">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 mr-2">Nível de urgência:</span>
                        <div class="flex space-x-1">
                            @for($i = 1; $i <= 3; $i++)
                                <div class="w-2 h-6 rounded-full {{ $i <= ($aiAnalysis['alerts']['urgency'] ?? 0) ? 'bg-red-500' : 'bg-gray-200' }}"></div>
                            @endfor
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
let isRefreshing = false;

function refreshInsights() {
    if (isRefreshing) return;
    
    const loader = document.getElementById('insights-loader');
    const content = document.getElementById('insights-content');
    const refreshIcon = document.getElementById('refresh-icon');
    const refreshText = document.getElementById('refresh-text');
    const refreshBtn = document.getElementById('refresh-insights-btn');
    
    // Inicia o estado de loading
    isRefreshing = true;
    loader.classList.remove('hidden');
    content.classList.add('opacity-50');
    refreshIcon.classList.add('animate-spin');
    refreshText.textContent = 'Atualizando...';
    refreshBtn.disabled = true;
    
    // Faz a requisição para atualizar os insights
    fetch(`/analytics/refresh/${businessId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro ao atualizar insights');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateInsightCards(data.aiAnalysis);
            showNotification('Insights atualizados com sucesso!', 'success');
        } else {
            throw new Error(data.message || 'Erro ao atualizar insights');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification(error.message, 'error');
    })
    .finally(() => {
        // Restaura o estado original
        isRefreshing = false;
        loader.classList.add('hidden');
        content.classList.remove('opacity-50');
        refreshIcon.classList.remove('animate-spin');
        refreshText.textContent = 'Atualizar Insights';
        refreshBtn.disabled = false;
    });
}

function updateInsightCards(aiAnalysis) {
    const content = document.getElementById('insights-content');
    
    // Template para cada tipo de card
    const templates = {
        performance: data => `
            <div class="group bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-blue-100 transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900">Performance</h4>
                            <span class="text-sm text-gray-500">Análise de desempenho</span>
                        </div>
                    </div>
                    <p class="text-gray-600 leading-relaxed">${data.message}</p>
                    ${data.score ? `
                    <div class="mt-4 pt-4 border-t border-gray-50">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Score geral</span>
                            <span class="font-medium text-blue-600">${data.score}</span>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `,
        opportunities: data => `
            <div class="group bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-green-100 transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900">Oportunidades</h4>
                            <span class="text-sm text-gray-500">Potenciais melhorias</span>
                        </div>
                    </div>
                    <p class="text-gray-600 leading-relaxed">${data.message}</p>
                    ${data.items ? `
                    <div class="mt-4 space-y-2">
                        ${data.items.map(item => `
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                </svg>
                                ${item}
                            </div>
                        `).join('')}
                    </div>
                    ` : ''}
                </div>
            </div>
        `,
        alerts: data => `
            <div class="group bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-red-100 transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900">Alertas</h4>
                            <span class="text-sm text-gray-500">Pontos de atenção</span>
                        </div>
                    </div>
                    <p class="text-gray-600 leading-relaxed">${data.message}</p>
                    ${data.urgency ? `
                    <div class="mt-4 pt-4 border-t border-gray-50">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-500 mr-2">Nível de urgência:</span>
                            <div class="flex space-x-1">
                                ${Array(3).fill(0).map((_, i) => `
                                    <div class="w-2 h-6 rounded-full ${i < data.urgency ? 'bg-red-500' : 'bg-gray-200'}"></div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `
    };

    // Atualiza o conteúdo
    let newContent = '';
    Object.entries(aiAnalysis).forEach(([key, data]) => {
        if (templates[key]) {
            newContent += templates[key](data);
        }
    });
    
    content.innerHTML = newContent;
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } shadow-lg transform transition-all duration-300 z-50`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'}
            </svg>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animação de entrada
    setTimeout(() => {
        notification.classList.add('translate-y-0', 'opacity-100');
    }, 100);
    
    // Remove após 3 segundos
    setTimeout(() => {
        notification.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.3s ease-out;
}
</style>
@endpush

 <!-- Análise de Concorrentes -->
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
        <!-- Cabeçalho Aprimorado -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 mb-8">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Análise de Concorrentes
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500">Monitoramento competitivo em tempo real</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hidden sm:inline-block">
                    Última atualização: {{ $lastUpdate ?? 'Hoje' }}
                </span>
                <button onclick="refreshCompetitorAnalysis()" 
                        class="group inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2 group-hover:animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Atualizar Análise
                </button>
            </div>
        </div>

        <!-- Estado de Carregamento Aprimorado -->
        <div id="competitor-loading" class="hidden">
            <div class="flex flex-col items-center justify-center py-12">
                <div class="relative">
                    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-600">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="h-10 w-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-700">Analisando concorrentes...</h3>
                    <div class="flex items-center justify-center space-x-2 mt-2">
                        <span class="text-sm text-gray-500">Coletando dados</span>
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Conteúdo Principal -->
        <div id="competitor-content" class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <!-- Lista de Concorrentes (9 colunas) -->
            <div class="xl:col-span-9">
                <div class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Principais Concorrentes</h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">Total encontrado:</span>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">
                                {{ count($competitors ?? []) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-6" id="top-competitors">
    @foreach($competitors ?? [] as $competitor)
    <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 group">
        <div class="flex flex-col sm:flex-row gap-6">
            <!-- Imagem e Status -->
            <div class="relative flex-shrink-0">
                <img 
                    src="{{ 
                        $competitor['thumbnailUrl'] ?? 
                        $competitor['serper_image'] ?? 
                        $competitor['image_url'] ?? 
                        asset('images/default-business.jpg')
                    }}"
                    alt="{{ $competitor['title'] ?? 'Concorrente' }}"
                    class="w-24 h-24 object-cover rounded-xl shadow-md group-hover:shadow-lg transition-all duration-300"
                    onerror="this.src='{{ asset('images/default-business.jpg') }}';"
                >
                <div class="absolute -top-2 -right-2 flex items-center justify-center w-6 h-6 bg-gradient-to-r {{ $competitor['status'] === 'active' ? 'from-green-500 to-emerald-600' : 'from-gray-400 to-gray-500' }} rounded-full shadow-lg border-2 border-white transform group-hover:scale-110 transition-all duration-300"
                     title="{{ $competitor['status'] === 'active' ? 'Ativo' : 'Inativo' }}">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $competitor['status'] === 'active' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                    </svg>
                </div>
            </div>
                                <!-- Informações do Concorrente -->
                                <div class="flex-grow">
                                    <div class="flex flex-wrap justify-between items-start gap-3">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors duration-300">
                                                {{ $competitor['title'] }}
                                            </h4>
                                            <p class="text-sm text-gray-600 flex items-center mt-2">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                {{ $competitor['location'] }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $competitor['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $competitor['status'] === 'active' ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </div>

                                    <!-- Sistema de Avaliação Aprimorado -->
                                    <div class="flex items-center mt-4">
                                        <div class="flex items-center">
                                            <div class="flex -space-x-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= floor($competitor['rating']))
                                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endif
                                                @endfor
                                            </div>
                                            <div class="ml-3">
                                                <span class="text-sm font-medium text-gray-700">{{ number_format($competitor['rating'], 1) }}/5</span>
                                                @if($competitor['reviews'])
                                                    <span class="mx-2 text-gray-400">•</span>
                                                    <span class="text-sm text-gray-500">{{ number_format($competitor['reviews']) }} avaliações</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Ações do Concorrente -->
                                    <div class="flex flex-wrap gap-3 mt-4">
                                        @if($competitor['phone'])
                                            <a href="tel:{{ $competitor['phone'] }}" 
                                               class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 transition-all duration-300 transform hover:scale-105 hover:shadow-md">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                Contatar
                                            </a>
                                        @endif
                                        
                                        @if($competitor['website'])
                                            <a href="{{ $competitor['website'] }}" 
                                               target="_blank" 
                                               class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 text-white hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 hover:shadow-md">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                Visitar site
                                            </a>
                                        @endif

                                        <button class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all duration-300">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Mais detalhes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Seção de Concorrentes -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold mb-4">Principais Concorrentes</h3>
    <p class="text-sm text-gray-600 mb-4">Baseado na sua localização</p>

    @if(!empty($analyticsData['competitors']))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($analyticsData['competitors'] as $competitor)
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold">{{ $competitor['name'] }}</h4>
                    <div class="flex items-center mt-2">
                        <div class="flex items-center text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $competitor['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="ml-2 text-sm text-gray-600">
                            {{ number_format($competitor['rating'], 1) }} 
                            ({{ $competitor['reviews'] }} avaliações)
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">{{ $competitor['address'] }}</p>
                    <p class="text-sm text-gray-500 mt-1">Distância: {{ $competitor['distance'] }}</p>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">Nenhum concorrente encontrado na região.</p>
            <p class="text-sm text-gray-400 mt-2">Verifique se as configurações de localização do seu negócio estão corretas.</p>
        </div>
    @endif
</div>
                    <!-- Paginação ou "Ver mais" -->
                    @if(count($competitors ?? []) > 5)
                        <div class="mt-8 text-center">
                            <button class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 rounded-xl text-indigo-600 hover:bg-indigo-50 transition-all duration-300">
                                <span>Ver mais concorrentes</span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Resumo e Estatísticas (3 colunas) -->
            <div class="xl:col-span-3 space-y-6">
               <!-- Card de Resumo -->
<div class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 rounded-xl p-6">
    <h4 class="text-lg font-semibold text-gray-800 mb-4">Resumo Competitivo</h4>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Total de Concorrentes analisados</span>
            <span class="font-semibold text-indigo-600">{{ $competitorsSummary['total'] }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Média de Avaliação</span>
            <div class="flex items-center">
                <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="font-semibold text-indigo-600">{{ $competitorsSummary['average_rating'] }}</span>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Concorrentes Ativos</span>
            <span class="font-semibold text-green-600">{{ $competitorsSummary['active_percentage'] }}%</span>
        </div>
    </div>
</div>

 <!-- Ações Rápidas -->
<div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-lg hover:shadow-xl transition-shadow duration-300">
    <div class="flex items-center justify-between mb-6">
        <h4 class="text-xl font-semibold text-gray-800">Ações Rápidas</h4>
        <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">2 ações disponíveis</span>
    </div>
    
    <div class="space-y-4">
        <!-- Gerar Relatório Button -->
        <button onclick="exportCompetitorAnalysis()" class="group w-full flex items-center justify-between px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 text-indigo-700 rounded-xl hover:from-indigo-100 hover:to-blue-100 transition-all duration-300 border border-indigo-100">
    <div class="flex items-center">
        <div class="bg-white p-2 rounded-lg shadow-sm group-hover:shadow-md transition-shadow duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
        </div>
        <div class="ml-4 text-left">
            <span class="block font-semibold">Exportar Análise</span>
            <span class="text-xs text-indigo-600 opacity-75">Exportar dados em PDF</span>
        </div>
    </div>
    <svg class="w-5 h-5 text-indigo-400 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</button>

        <!-- Análise Detalhada Button -->
        <button 
            x-data
            @click="$dispatch('open-modal', 'competitor-analysis')"
            class="group w-full flex items-center justify-between px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition-all duration-300 border border-purple-100"
        >
            <div class="flex items-center">
                <div class="bg-white p-2 rounded-lg shadow-sm group-hover:shadow-md transition-shadow duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                    </svg>
                </div>
                <div class="ml-4 text-left">
                    <span class="block font-semibold">Análise Detalhada</span>
                    <span class="text-xs text-purple-600 opacity-75">Analisar concorrentes</span>
                </div>
            </div>
            <svg class="w-5 h-5 text-purple-400 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</div>

<x-modal name="competitor-analysis" :maxWidth="'4xl'">
    <div class="bg-white p-4 sm:p-6 lg:p-8 rounded-xl">
        <!-- Header com gradiente -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 pb-4 sm:pb-6 mb-4 sm:mb-6">
            <div>
                <h3 class="text-xl sm:text-2xl font-google-sans font-bold text-gray-800 mb-2">
                    {{ $competitorAnalysis['title'] ?? 'Análise Detalhada' }}
                </h3>
                <p class="text-sm text-gray-500 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Última atualização: {{ now()->format('d/m/Y H:i') }}
                </p>
            </div>
            <button @click="show = false" 
                    class="mt-4 sm:mt-0 p-2 hover:bg-gray-100 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="grid grid-cols-1 gap-6">
            <!-- Main Analysis -->
            <div class="space-y-6">
                <!-- AI Response Section -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center font-google-sans">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Análise da IA
                    </h4>
                    <div class="prose max-w-none text-gray-700 text-base leading-relaxed animate-fadeIn">
                        {!! preg_replace(
                            ['/\*\*(.*?)\*\*/', '/\*(.*?)\*/', '/- (.*?)(\n|$)/', '/\b(importante|atenção|destaque):/i'],
                            [
                                '<span class="font-bold text-blue-800">$1</span>',
                                '<span class="italic text-gray-800">$1</span>',
                                '<div class="flex items-start space-x-2 my-2">
                                    <span class="flex-shrink-0 w-1.5 h-1.5 mt-2 bg-blue-500 rounded-full"></span>
                                    <span>$1</span>
                                </div>',
                                '<span class="font-semibold text-red-600">$1:</span>'
                            ],
                            nl2br(e($competitorAnalysis['content'] ?? ''))
                        ) !!}
                    </div>
                </div>

                <!-- Métricas Grid com Animação -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-fadeIn">
                    <!-- Posição Média -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 transform hover:scale-105 transition-all duration-300 hover:shadow-lg">
                        <span class="text-sm text-blue-600 font-medium font-google-sans">Posição Média</span>
                        <div class="flex items-baseline mt-2">
                            <p class="text-2xl font-bold text-blue-700">
                                {{ number_format($metrics['average_position'] ?? 0, 1) }}
                            </p>
                            @if(isset($metrics['position_trend']))
                                <span class="ml-2 text-sm {{ $metrics['position_trend'] > 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $metrics['position_trend'] > 0 ? '↑' : '↓' }} 
                                    {{ abs($metrics['position_trend']) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Avaliações -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 transform hover:scale-105 transition-all duration-300 hover:shadow-lg">
                        <span class="text-sm text-green-600 font-medium font-google-sans">Avaliações</span>
                        <div class="flex items-baseline mt-2">
                            <p class="text-2xl font-bold text-green-700">
                                {{ number_format($metrics['rating'] ?? 0, 1) }}
                            </p>
                            @if(isset($metrics['rating_trend']))
                                <span class="ml-2 text-sm {{ $metrics['rating_trend'] > 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $metrics['rating_trend'] > 0 ? '↑' : '↓' }} 
                                    {{ abs($metrics['rating_trend']) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Engajamento -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 transform hover:scale-105 transition-all duration-300 hover:shadow-lg">
                        <span class="text-sm text-purple-600 font-medium font-google-sans">Engajamento</span>
                        <div class="flex items-baseline mt-2">
                            <p class="text-2xl font-bold text-purple-700">
                                {{ number_format($metrics['engagement_rate'] ?? 0, 0) }}%
                            </p>
                            @if(isset($metrics['engagement_trend']))
                                <span class="ml-2 text-sm {{ $metrics['engagement_trend'] > 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $metrics['engagement_trend'] > 0 ? '↑' : '↓' }} 
                                    {{ abs($metrics['engagement_trend']) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out;
    }
</style>

@push('scripts')
<script>

function exportCompetitorAnalysis() {
    const businessId = document.getElementById('business-selector').value;
    
    // Mostrar indicador de carregamento
    Swal.fire({
        title: 'Gerando relatório...',
        text: 'Por favor, aguarde...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fazer a requisição para gerar o PDF
    fetch(`/analytics/export-competitor-analysis/${businessId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro ao gerar relatório');
        return response.blob();
    })
    .then(blob => {
        // Criar URL do blob e fazer download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'analise-concorrentes.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        
        Swal.fire({
            icon: 'success',
            title: 'Relatório gerado com sucesso!',
            showConfirmButton: false,
            timer: 1500
        });
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Erro ao gerar o relatório. Tente novamente.'
        });
    });
}

function exportAnalysisReport() {
    const businessId = document.getElementById('business-selector').value;
    
    // Mostrar indicador de carregamento
    Swal.fire({
        title: 'Gerando relatório...',
        text: 'Por favor, aguarde...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fazer a requisição para gerar o PDF
    fetch(`/analytics/export-analysis/${businessId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro ao gerar relatório');
        return response.blob();
    })
    .then(blob => {
        // Criar URL do blob e fazer download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'analise-detalhada.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        
        Swal.fire({
            icon: 'success',
            title: 'Relatório gerado com sucesso!',
            showConfirmButton: false,
            timer: 1500
        });
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Erro ao gerar o relatório. Tente novamente.'
        });
    });
}

// Função para exportar relatório
function exportReport(businessId) {
    console.log('Iniciando exportação...');
    
    // Captura o elemento com a classe analysis-content
    const analysisContent = document.querySelector('.analysis-content');
    
    // Verifica se o elemento existe
    if (!analysisContent) {
        console.error('Elemento .analysis-content não encontrado');
        showNotification('Erro: Conteúdo da análise não encontrado', 'error');
        return;
    }

    // Captura as métricas
    const metrics = {
        views: document.querySelector('[data-metric="views"]')?.textContent.trim(),
        clicks: document.querySelector('[data-metric="clicks"]')?.textContent.trim(),
        calls: document.querySelector('[data-metric="calls"]')?.textContent.trim()
    };

    // Verifica se todas as métricas foram encontradas
    if (!metrics.views || !metrics.clicks || !metrics.calls) {
        console.error('Uma ou mais métricas não encontradas');
        showNotification('Erro: Dados incompletos', 'error');
        return;
    }

    // Mostra notificação de processamento
    showNotification('Gerando relatório...', 'info');

    // Faz a requisição para gerar o PDF
    fetch(`/analytics/export/${businessId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            content: analysisContent.innerHTML,
            metrics: metrics
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro na resposta do servidor');
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `analise_${new Date().toISOString().split('T')[0]}.pdf`;
        document.body.appendChild(a);
        a.click();
        
        setTimeout(() => {
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }, 100);

        showNotification('Relatório gerado com sucesso!', 'success');
    })
    .catch(error => {
        console.error('Erro ao gerar relatório:', error);
        showNotification('Erro ao gerar relatório. Tente novamente.', 'error');
    });
}

// Função de notificação
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    } animate-fade-in z-50`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('animate-fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Inicialização dos listeners do modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('event-modal');
    if (!modal) return;

    const closeButtons = modal.querySelectorAll('.modal-close');
    
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    });
    
    // Fecha o modal ao clicar fora dele
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>
@endpush
                <!-- Status Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 sm:p-6 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold text-gray-800">Status</h4>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                            Atualizado
                        </span>
                    </div>
                    <p class="text-gray-600 text-sm">
                        Próxima análise programada para {{ now()->addDays(7)->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>
        </div>
    </div>
</x-modal>


<!-- Scripts -->
<script>

    // Função para exportar relatório
function exportDetailedReport(type) {
    const businessId = document.getElementById('business-selector').value;
    const period = document.getElementById('period-selector').value;
    
    // Construir URL com parâmetros
    const url = `/analytics/export/${type}/${businessId}?period=${period}`;
    
    // Mostrar indicador de carregamento
    showLoadingIndicator();
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Erro ao exportar relatório');
    })
    .then(blob => {
        // Criar link para download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `relatorio-${type}-${new Date().toISOString().split('T')[0]}.${type}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        hideLoadingIndicator();
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro ao exportar relatório', 'error');
        hideLoadingIndicator();
    });
}

// Função para fechar modal
function closeModal(element) {
    const modal = element.closest('.fixed');
    modal.remove();
}

// Função para submeter agendamento
function submitReviewSchedule(event) {
    event.preventDefault();
    
    const form = event.target;
    const businessId = document.getElementById('business-selector').value;
    const formData = new FormData(form);
    
    fetch(`/analytics/schedule-review/${businessId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            review_date: formData.get('review_date'),
            notes: formData.get('notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Revisão agendada com sucesso!', 'success');
            closeModal(form);
        } else {
            showNotification('Erro ao agendar revisão', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro ao agendar revisão', 'error');
    });
}

// Função auxiliar para mostrar notificações
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } animate-fade-in z-50`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Funções auxiliares para indicador de carregamento
function showLoadingIndicator() {
    // Implementar indicador de carregamento
}

function hideLoadingIndicator() {
    // Implementar remoção do indicador de carregamento
}

function fetchCompetitors() {
    fetch(`/analytics/detailed-analysis/{{ $business->id }}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.competitors = data.data;
            } else {
                alert('Erro ao carregar dados dos concorrentes');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar dados dos concorrentes');
        });
}
</script>

<!-- Scripts JavaScript necessários -->
<script>
    function refreshCompetitorAnalysis() {
        // Mostrar loading
        document.getElementById('competitor-loading').classList.remove('hidden');
        document.getElementById('competitor-content').classList.add('opacity-50');

        // Simular chamada de API
        setTimeout(() => {
            // Esconder loading
            document.getElementById('competitor-loading').classList.add('hidden');
            document.getElementById('competitor-content').classList.remove('opacity-50');
        }, 2000);
    }

    function refreshMarketAnalysis() {
        // Mostrar loading
        document.getElementById('market-analysis-loading').classList.remove('hidden');
        document.getElementById('market-analysis-content').classList.add('opacity-50');

        // Simular chamada de API
        setTimeout(() => {
            // Esconder loading
            document.getElementById('market-analysis-loading').classList.add('hidden');
            document.getElementById('market-analysis-content').classList.remove('opacity-50');
        }, 2000);
    }
</script>


@push('scripts')
<script>

     // Função que será executada quando a página carregar
     document.addEventListener('DOMContentLoaded', function() {
        // Inicia análise de concorrentes automaticamente
        refreshCompetitorAnalysis();
        
        // Inicia análise de mercado automaticamente
        refreshMarketAnalysis();
        
        // Se houver análise Gemini, também pode iniciar automaticamente
        updateGeminiAnalysis();
    });

// Funções JavaScript atualizadas
function refreshCompetitorAnalysis() {
    // Elementos do DOM
    const businessId = document.getElementById('business-selector').value;
    const loadingElement = document.getElementById('competitor-loading');
    const contentElement = document.getElementById('competitor-content');
    const recommendationsElement = document.getElementById('strategic-recommendations');

    // Validação inicial
    if (!businessId) {
        console.error('ID do negócio não encontrado');
        return;
    }

    // Mostra loading e adiciona opacity
    loadingElement.classList.remove('hidden');
    contentElement.classList.add('opacity-50');

    // Faz a requisição
    fetch('/competitor-analysis/analyze', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ business_id: businessId })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('Dados recebidos:', data); // Log para debug

        if (!data.success) throw new Error(data.message || 'Erro ao atualizar análise');

        // Atualiza a seção de concorrentes
        if (data.competitors && Array.isArray(data.competitors)) {
            const topCompetitorsElement = document.getElementById('top-competitors');
            if (topCompetitorsElement) {
                topCompetitorsElement.innerHTML = data.competitors
                    .map(competitor => `
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex space-x-4">
                                <div class="flex-shrink-0">
                                    <img src="${competitor.image_url || '/images/default-business.jpg'}"
                                         alt="${competitor.title}"
                                         class="w-20 h-20 object-cover rounded-lg"
                                         onerror="this.src='/images/default-business.jpg'">
                                </div>
                                <div class="flex-grow">
                                    <h4 class="font-medium text-gray-900">${competitor.title}</h4>
                                    <p class="text-sm text-gray-500">${competitor.location}</p>
                                    <div class="flex items-center mt-2">
                                        <div class="flex text-yellow-400">
                                            ${generateStarRating(competitor.rating)}
                                            <span class="ml-2 text-sm text-gray-600">
                                                ${competitor.rating.toFixed(1)}/5
                                                ${competitor.reviews ? `(${competitor.reviews} avaliações)` : ''}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                                        ${competitor.phone ? `
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                ${competitor.phone}
                                            </span>
                                        ` : ''}
                                        ${competitor.website ? `
                                            <a href="${competitor.website}" target="_blank" class="flex items-center text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                Visitar site
                                            </a>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
            }
        }

        // Atualiza a análise de mercado
        if (data.marketAnalysis && Array.isArray(data.marketAnalysis)) {
            const marketAnalysisElement = document.getElementById('market-analysis');
            if (marketAnalysisElement) {
                marketAnalysisElement.innerHTML = data.marketAnalysis
                    .map(analysis => `
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <h4 class="font-medium text-gray-900 mb-2">${analysis.title}</h4>
                            <p class="text-sm text-gray-600">${analysis.description}</p>
                            ${analysis.metrics ? `
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    ${analysis.metrics.map(metric => `
                                        <div class="bg-gray-50 p-2 rounded">
                                            <span class="text-xs text-gray-500">${metric.label}</span>
                                            <div class="font-medium">${metric.value}</div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : ''}
                        </div>
                    `).join('');
            }
        }

        // Atualiza recomendações estratégicas
        if (data.recommendations && Array.isArray(data.recommendations)) {
            if (recommendationsElement) {
                recommendationsElement.innerHTML = data.recommendations
                    .map(recommendation => `
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 ${getPriorityColor(recommendation.priority)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">${recommendation.title}</h4>
                                    <p class="text-sm text-gray-500 mt-1">${recommendation.description}</p>
                                    ${recommendation.priority ? `
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 ${getPriorityClass(recommendation.priority)}">
                                            ${recommendation.priority.charAt(0).toUpperCase() + recommendation.priority.slice(1)} Priority
                                        </span>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('');
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar análise: ' + error.message);
    })
    .finally(() => {
        loadingElement.classList.add('hidden');
        contentElement.classList.remove('opacity-50');
    });
}

// Funções auxiliares
function generateStarRating(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) {
            stars += '<svg class="w-4 h-4 fill-current text-yellow-400" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
        } else if (i - 0.5 <= rating) {
            stars += '<svg class="w-4 h-4 fill-current text-yellow-400" viewBox="0 0 24 24"><path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4V6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/></svg>';
        } else {
            stars += '<svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
        }
    }
    return stars;
}

function getPriorityColor(priority) {
    return {
        'high': 'text-red-500',
        'medium': 'text-yellow-500',
        'low': 'text-green-500'
    }[priority] || 'text-blue-500';
}

function getPriorityClass(priority) {
    return {
        'high': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800'
    }[priority] || 'bg-blue-100 text-blue-800';
}

function updateCompetitorContent(data) {
    try {

        
        // Update competitors section
        const topCompetitorsElement = document.getElementById('top-competitors');
        if (data.competitors && Array.isArray(data.competitors) && topCompetitorsElement) {
            topCompetitorsElement.innerHTML = data.competitors
                .filter(competitor => competitor) // Remove null/undefined entries
                .map(competitor => {
                    try {
                        return createCompetitorCard(competitor);
                    } catch (error) {
                        console.error('Error creating competitor card:', error);
                        return ''; // Return empty string if card creation fails
                    }
                })
                .join('');
        } else {
            if (topCompetitorsElement) {
                topCompetitorsElement.innerHTML = '<div class="p-4 text-gray-500">Nenhum concorrente encontrado.</div>';
            }
        }

        // Update market analysis section
        const marketAnalysisElement = document.getElementById('market-analysis');
        if (data.marketAnalysis && Array.isArray(data.marketAnalysis) && marketAnalysisElement) {
            marketAnalysisElement.innerHTML = data.marketAnalysis
                .filter(analysis => analysis) // Remove null/undefined entries
                .map(analysis => {
                    try {
                        return createMarketAnalysisCard(analysis);
                    } catch (error) {
                        console.error('Error creating market analysis card:', error);
                        return ''; // Return empty string if card creation fails
                    }
                })
                .join('');
        } else {
            if (marketAnalysisElement) {
                marketAnalysisElement.innerHTML = '<div class="p-4 text-gray-500">Nenhuma análise de mercado disponível.</div>';
            }
        }

        

        // Update recommendations section
        const recommendationsElement = document.getElementById('strategic-recommendations');
        if (data.recommendations && Array.isArray(data.recommendations) && recommendationsElement) {
            recommendationsElement.innerHTML = data.recommendations
                .filter(recommendation => recommendation) // Remove null/undefined entries
                .map(recommendation => {
                    try {
                        return createRecommendationCard(recommendation);
                    } catch (error) {
                        console.error('Error creating recommendation card:', error);
                        return ''; // Return empty string if card creation fails
                    }
                })
                .join('');
        } else {
            if (recommendationsElement) {
                recommendationsElement.innerHTML = '<div class="p-4 text-gray-500">Nenhuma recomendação disponível.</div>';
            }
        }

        // Add success notification
        if (typeof showNotification === 'function') {
            showNotification('Análise atualizada com sucesso!', 'success');
        }

    } catch (error) {
        console.error('Error updating competitor content:', error);
        
        // Show error notification if available
        if (typeof showNotification === 'function') {
            showNotification('Erro ao atualizar análise. Por favor, tente novamente.', 'error');
        }
        
        // Reset loading state if needed
        const loadingElement = document.getElementById('competitor-loading');
        if (loadingElement) {
            loadingElement.classList.add('hidden');
        }
        
        // Reset content opacity if needed
        const contentElement = document.getElementById('competitor-content');
        if (contentElement) {
            contentElement.classList.remove('opacity-50');
        }
    }
}

// Helper function to safely get element by ID
function getElementByIdSafely(id) {
    const element = document.getElementById(id);
    if (!element) {
        console.warn(`Element with id '${id}' not found`);
    }
    return element;
}

// Helper function to validate data array
function isValidDataArray(data) {
    return data && Array.isArray(data) && data.length > 0;
}

// Helper function to show empty state message
function showEmptyState(element, message) {
    if (element) {
        element.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2">${message}</p>
            </div>
        `;
    }
}

// Helper function to create competitor card
function createCompetitorCard(competitor) {
    return `
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex space-x-4">
                <div class="flex-shrink-0">
                    <img src="${competitor.image_url || '/images/default-business.jpg'}"
                         alt="${competitor.title}"
                         class="w-20 h-20 object-cover rounded-lg"
                         onerror="this.src='/images/default-business.jpg'">
                </div>
                <div class="flex-grow">
                    <h4 class="font-medium text-gray-900">${competitor.title}</h4>
                    <p class="text-sm text-gray-500">${competitor.location || 'Localização não disponível'}</p>
                    <div class="flex items-center mt-2">
                        <div class="flex text-yellow-400">
                            ${generateStarRating(competitor.rating)}
                            <span class="ml-2 text-sm text-gray-600">
                                ${competitor.rating.toFixed(1)}/5
                                ${competitor.reviews ? `(${competitor.reviews} avaliações)` : ''}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                        ${competitor.phone ? `
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                ${competitor.phone}
                            </span>
                        ` : ''}
                        ${competitor.website ? `
                            <a href="${competitor.website}" 
                               target="_blank" 
                               class="flex items-center text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Visitar site
                            </a>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Helper function to create market analysis card
function createMarketAnalysisCard(analysis) {
    return `
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <h4 class="font-medium text-gray-900 mb-2">${analysis.title}</h4>
            <p class="text-sm text-gray-600">${analysis.description}</p>
            ${analysis.metrics ? `
                <div class="mt-2 grid grid-cols-2 gap-2">
                    ${analysis.metrics.map(metric => `
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="text-xs text-gray-500">${metric.label}</span>
                            <div class="font-medium">${metric.value}</div>
                        </div>
                    `).join('')}
                </div>
            ` : ''}
        </div>
    `;
}

// Helper function to create recommendation card with Gemini analysis
function createRecommendationCard(recommendation) {
    const priorityColors = {
        high: 'text-red-500',
        medium: 'text-yellow-500',
        low: 'text-green-500'
    };

    const priorityBgColors = {
        high: 'bg-red-100 text-red-800',
        medium: 'bg-yellow-100 text-yellow-800',
        low: 'bg-green-100 text-green-800'
    };

    return `
        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 ${priorityColors[recommendation.priority] || 'text-gray-500'}" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-900">${recommendation.title}</h4>
                    <p class="text-sm text-gray-500 mt-1">${recommendation.description}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 
                          ${priorityBgColors[recommendation.priority] || 'bg-gray-100 text-gray-800'}">
                        ${recommendation.priority ? recommendation.priority.charAt(0).toUpperCase() + recommendation.priority.slice(1) : 'Normal'} Priority
                    </span>
                </div>
            </div>
        </div>
    `;
}

// Helper function to generate star rating
function generateStarRating(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) {
            stars += `<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>`;
        } else if (i - 0.5 <= rating) {
            stars += `<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4V6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/></svg>`;
        } else {
            stars += `<svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>`;
        }
    }
    return stars;
}

// Main function to update competitor content
function updateCompetitorContent(data) {
    try {
        // Update competitors section
        const topCompetitorsElement = document.getElementById('top-competitors');
        if (data.competitors && Array.isArray(data.competitors) && topCompetitorsElement) {
            topCompetitorsElement.innerHTML = data.competitors
                .filter(competitor => competitor)
                .map(competitor => {
                    try {
                        return createCompetitorCard(competitor);
                    } catch (error) {
                        console.error('Error creating competitor card:', error);
                        return '';
                    }
                })
                .join('');
        } else {
            if (topCompetitorsElement) {
                topCompetitorsElement.innerHTML = '<div class="p-4 text-gray-500">Nenhum concorrente encontrado.</div>';
            }
        }

        // Update market analysis section
        const marketAnalysisElement = document.getElementById('market-analysis');
        if (data.marketAnalysis && Array.isArray(data.marketAnalysis) && marketAnalysisElement) {
            marketAnalysisElement.innerHTML = data.marketAnalysis
                .filter(analysis => analysis)
                .map(analysis => {
                    try {
                        return createMarketAnalysisCard(analysis);
                    } catch (error) {
                        console.error('Error creating market analysis card:', error);
                        return '';
                    }
                })
                .join('');
        } else {
            if (marketAnalysisElement) {
                marketAnalysisElement.innerHTML = '<div class="p-4 text-gray-500">Nenhuma análise de mercado disponível.</div>';
            }
        }

        // Update recommendations section with Gemini analysis
        const recommendationsElement = document.getElementById('strategic-recommendations');
        if (data.recommendations && Array.isArray(data.recommendations) && recommendationsElement) {
            recommendationsElement.innerHTML = data.recommendations
                .filter(recommendation => recommendation)
                .map(recommendation => {
                    try {
                        return createRecommendationCard(recommendation);
                    } catch (error) {
                        console.error('Error creating recommendation card:', error);
                        return '';
                    }
                })
                .join('');
        } else {
            if (recommendationsElement) {
                recommendationsElement.innerHTML = '<div class="p-4 text-gray-500">Nenhuma recomendação disponível.</div>';
            }
        }

        // Add success notification
        if (typeof showNotification === 'function') {
            showNotification('Análise atualizada com sucesso!', 'success');
        }

    } catch (error) {
        console.error('Error updating competitor content:', error);
        
        if (typeof showNotification === 'function') {
            showNotification('Erro ao atualizar análise. Por favor, tente novamente.', 'error');
        }
        
        const loadingElement = document.getElementById('competitor-loading');
        if (loadingElement) {
            loadingElement.classList.add('hidden');
        }
        
        const contentElement = document.getElementById('competitor-content');
        if (contentElement) {
            contentElement.classList.remove('opacity-50');
        }
    }
}
</script>



@endpush

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

    async function refreshCompetitorAnalysis() {
    try {
        const loadingElement = document.getElementById('competitor-loading');
        const contentElement = document.getElementById('competitor-content');
        
        loadingElement.classList.remove('hidden');
        contentElement.classList.add('opacity-50');
        
        const response = await fetch('/competitor-analysis/analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                business_id: businessId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Atualiza a view com os dados
            updateCompetitorContent(data);
        } else {
            throw new Error(data.message || 'Erro ao atualizar análise');
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro ao atualizar análise: ' + error.message, 'error');
    } finally {
        loadingElement.classList.add('hidden');
        contentElement.classList.remove('opacity-50');
    }
}

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

<script>
function refreshMarketAnalysis() {
    const loadingElement = document.getElementById('market-analysis-loading');
    const contentElement = document.getElementById('market-analysis-content');
    const businessId = document.getElementById('business-selector').value;

    // Mostra loading
    loadingElement.classList.remove('hidden');
    contentElement.classList.add('opacity-50');

    // Faz a requisição
    fetch(`/market-analysis/${businessId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    })
    .then(data => {
        // Verifica se os dados existem antes de usar
        const marketOverview = data.market_overview || 'Análise não disponível';
        const competitorAnalysis = data.competitor_analysis || 'Análise não disponível';
        const opportunities = data.opportunities || 'Análise não disponível';
        const recommendations = data.recommendations || 'Análise não disponível';

        // Cria o HTML para a análise
        const html = `
            <div class="space-y-6">
                <div class="bg-blue-50 p-6 rounded-lg border-l-4 border-blue-500">
                    <h3 class="text-lg font-semibold text-blue-800 mb-3">Visão Geral do Mercado</h3>
                    <div class="prose prose-blue max-w-none">
                        <p class="text-gray-700 leading-relaxed">${formatAnalysisText(marketOverview)}</p>
                    </div>
                </div>

<div class="bg-purple-50 p-6 rounded-lg border-l-4 border-purple-500">
    <h3 class="text-lg font-semibold text-purple-800 mb-3">Análise dos Concorrentes</h3>
    <div class="prose prose-purple max-w-none">
        <p class="text-gray-700 leading-relaxed">
            @if(isset($competitorAnalysis) && !empty($competitorAnalysis))
                @if(isset($competitorAnalysis['title']))
                    <h4 class="text-lg font-medium mb-2">{{ $competitorAnalysis['title'] }}</h4>
                @endif
                @if(isset($competitorAnalysis['content']))
                    {!! nl2br(e($competitorAnalysis['content'])) !!}
                @endif
            @else
                Nenhuma análise de concorrentes disponível no momento.
            @endif
        </p>
    </div>
</div>

                <div class="bg-green-50 p-6 rounded-lg border-l-4 border-green-500">
                    <h3 class="text-lg font-semibold text-green-800 mb-3">Oportunidades Identificadas</h3>
                    <div class="prose prose-green max-w-none">
                        <p class="text-gray-700 leading-relaxed">${formatAnalysisText(opportunities)}</p>
                    </div>
                </div>

                <div class="bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-500">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-3">Recomendações Estratégicas</h3>
                    <div class="prose prose-yellow max-w-none">
                        <p class="text-gray-700 leading-relaxed">${formatAnalysisText(recommendations)}</p>
                    </div>
                </div>
            </div>
        `;

        // Atualiza o conteúdo
        contentElement.innerHTML = html;
    })
    .catch(error => {
        console.error('Erro:', error);
        contentElement.innerHTML = `
            <div class="bg-red-50 p-6 rounded-lg border-l-4 border-red-500">
                <h3 class="text-lg font-semibold text-red-800 mb-3">Erro</h3>
                <p class="text-red-700">Não foi possível carregar a análise de mercado. Por favor, tente novamente.</p>
            </div>
        `;
    })
    .finally(() => {
        // Esconde loading
        loadingElement.classList.add('hidden');
        contentElement.classList.remove('opacity-50');
    });
}

// Função auxiliar para formatar o texto da análise
function formatAnalysisText(text) {
    return text
        .replace(/\n/g, '</p><p class="text-gray-700 leading-relaxed">')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/- (.*?)(?=\n|$)/g, '<li>$1</li>')
        .replace(/<li>/g, '<ul class="list-disc pl-4 space-y-2"><li>')
        .replace(/<\/li>(?!\s*<li>)/g, '</li></ul>');
}

function updateGeminiAnalysis() {
    // Mostra loading
    document.getElementById('geminiLoading').classList.remove('hidden');
    document.getElementById('geminiContent').classList.add('opacity-50');

    // Faz a requisição
    fetch(`/analytics/update-gemini-analysis/${businessId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.message || 'Erro ao atualizar análise');
        }

        // Atualiza o conteúdo da análise de concorrentes
        const competitorAnalysis = document.getElementById('competitorAnalysis');
        if (data.competitor_analysis && data.competitor_analysis.length > 0) {
            competitorAnalysis.innerHTML = data.competitor_analysis
                .map(analysis => `<li class="mb-2">${analysis}</li>`)
                .join('');
        } else {
            competitorAnalysis.innerHTML = '<li>Nenhuma análise de concorrentes disponível.</li>';
        }

        // Atualiza timestamp
        document.getElementById('lastUpdate').textContent = 
            new Date().toLocaleString('pt-BR');

        // Mostra mensagem de sucesso
        alert('Análise atualizada com sucesso!');
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar análise. Tente novamente.');
    })
    .finally(() => {
        // Esconde loading
        document.getElementById('geminiLoading').classList.add('hidden');
        document.getElementById('geminiContent').classList.remove('opacity-50');
    });
}

</script>
@endpush
@include('automation.modals.event-modal')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carrega as palavras-chave quando a página é carregada
    loadKeywords();
});

function loadKeywords() {
    const businessId = document.getElementById('business-selector').value;
    
    // Mostra indicador de carregamento
    document.getElementById('keywords-container').innerHTML = `
        <div class="flex justify-center items-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
            <span class="ml-2 text-gray-600">Buscando palavras-chave...</span>
        </div>
    `;

    // Faz a requisição para buscar as palavras-chave
    fetch(`/analytics/${businessId}/keywords`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        displayKeywords(data);
        updateLastUpdate();
    })
    .catch(error => {
        document.getElementById('keywords-container').innerHTML = `
            <div class="text-center p-4 text-red-600">
                Erro ao carregar palavras-chave. Tente novamente.
            </div>
        `;
    });
}

function refreshKeywords() {
    const businessId = document.getElementById('business-selector').value;
    
    // Mostra indicador de carregamento
    document.getElementById('keywords-container').innerHTML = `
        <div class="flex justify-center items-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
            <span class="ml-2 text-gray-600">Atualizando palavras-chave...</span>
        </div>
    `;

    // Faz a requisição para atualizar as palavras-chave
    fetch(`/analytics/${businessId}/keywords/refresh`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        displayKeywords(data);
        updateLastUpdate();
    })
    .catch(error => {
        document.getElementById('keywords-container').innerHTML = `
            <div class="text-center p-4 text-red-600">
                Erro ao atualizar palavras-chave. Tente novamente.
            </div>
        `;
    });
}

function displayKeywords(keywords) {
    const container = document.getElementById('keywords-container');
    
    if (!keywords || Object.keys(keywords).length === 0) {
        container.innerHTML = `
            <div class="text-center p-4 text-gray-600">
                Nenhuma palavra-chave encontrada.
            </div>
        `;
        return;
    }

    let html = `<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">`;
    
    Object.entries(keywords).forEach(([term, data]) => {
        html += `
            <div class="bg-gradient-to-br from-indigo-50/50 to-white p-4 rounded-xl border border-indigo-100/50 hover:shadow-sm transition-all duration-300">
                <div class="flex flex-col">
                    <span class="text-sm text-gray-600 mb-1">${term}</span>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full">
                            ${typeof data === 'object' ? data.volume || 'N/A' : data}
                        </span>
                    </div>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    container.innerHTML = html;
}

function updateLastUpdate() {
    const lastUpdate = document.getElementById('last-update');
    if (lastUpdate) {
        lastUpdate.textContent = `Última atualização: ${new Date().toLocaleTimeString()}`;
    }
}
</script>
</x-app-layout>