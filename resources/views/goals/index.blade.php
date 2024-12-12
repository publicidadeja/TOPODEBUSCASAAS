<x-app-layout>
    <!-- Header Section -->
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white/80 backdrop-blur-sm border-b border-gray-100/50 p-4 rounded-xl shadow-sm">
            <!-- Left Side - Title and Info -->
            <div class="flex items-center space-x-3">
                <!-- Icon Container -->
                <div class="p-2 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>

                <!-- Title and Subtitle -->
                <div>
                    <h2 class="text-xl font-semibold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                        Metas - {{ $business->name }}
                    </h2>
                    <p class="text-sm text-gray-500">Defina e acompanhe suas metas mensais</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Current Month Goals Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Metas do Mês Atual
                        <span class="text-sm font-normal text-gray-500 ml-2">
                            {{ now()->format('F/Y') }}
                        </span>
                    </h3>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-6 animate-fade-in">
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="p-1 bg-green-100 rounded-full">
                                        <svg class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Goals Form -->
                <form action="{{ route('goals.store', $business) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Monthly Views Goal -->
                        <div class="space-y-2 group">
                            <label class="block text-sm font-medium text-gray-700">
                                Meta de Visualizações
                                <span class="ml-1 text-xs text-gray-500">(mensal)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </div>
                                <input type="number" 
                                       name="monthly_views_goal" 
                                       value="{{ old('monthly_views_goal', $currentGoal->monthly_views_goal ?? '') }}"
                                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            </div>
                            @error('monthly_views_goal')
                                <p class="text-sm text-red-600 animate-shake">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Monthly Clicks Goal -->
                        <div class="space-y-2 group">
                            <label class="block text-sm font-medium text-gray-700">
                                Meta de Cliques
                                <span class="ml-1 text-xs text-gray-500">(mensal)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                                    </svg>
                                </div>
                                <input type="number" 
                                       name="monthly_clicks_goal" 
                                       value="{{ old('monthly_clicks_goal', $currentGoal->monthly_clicks_goal ?? '') }}"
                                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            </div>
                            @error('monthly_clicks_goal')
                                <p class="text-sm text-red-600 animate-shake">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Conversion Rate Goal -->
                        <div class="space-y-2 group">
                            <label class="block text-sm font-medium text-gray-700">
                                Meta de Taxa de Conversão
                                <span class="ml-1 text-xs text-gray-500">(%)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <input type="number" 
                                       step="0.01" 
                                       name="conversion_rate_goal" 
                                       value="{{ old('conversion_rate_goal', $currentGoal->conversion_rate_goal ?? '') }}"
                                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            </div>
                            @error('conversion_rate_goal')
                                <p class="text-sm text-red-600 animate-shake">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-6 border-t border-gray-100">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $currentGoal ? 'Atualizar Metas' : 'Definir Metas' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Progress Card -->
            @if($currentGoal)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 hover:shadow-md transition-all duration-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">
                    Progresso do Mês Atual
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Views Progress -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <h4 class="text-sm font-medium text-gray-700">Visualizações</h4>
                            @php
                                $viewsProgress = $currentGoal->monthly_views_goal > 0 
                                    ? min(100, ($currentMonthAnalytics->views ?? 0) / $currentGoal->monthly_views_goal * 100) 
                                    : 0;
                            @endphp
                            <span class="text-sm text-gray-600">{{ number_format($viewsProgress, 1) }}%</span>
                        </div>
                        <div class="relative h-2 bg-blue-100 rounded-full overflow-hidden">
                            <div class="absolute top-0 left-0 h-full bg-blue-600 transition-all duration-500"
                                 style="width: {{ $viewsProgress }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600">
                            {{ number_format($currentMonthAnalytics->views ?? 0) }} de {{ number_format($currentGoal->monthly_views_goal) }}
                        </p>
                    </div>

                    <!-- Clicks Progress -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <h4 class="text-sm font-medium text-gray-700">Cliques</h4>
                            @php
                                $clicksProgress = $currentGoal->monthly_clicks_goal > 0 
                                    ? min(100, ($currentMonthAnalytics->clicks ?? 0) / $currentGoal->monthly_clicks_goal * 100) 
                                    : 0;
                            @endphp
                            <span class="text-sm text-gray-600">{{ number_format($clicksProgress, 1) }}%</span>
                        </div>
                        <div class="relative h-2 bg-green-100 rounded-full overflow-hidden">
                            <div class="absolute top-0 left-0 h-full bg-green-600 transition-all duration-500"
                                 style="width: {{ $clicksProgress }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600">
                            {{ number_format($currentMonthAnalytics->clicks ?? 0) }} de {{ number_format($currentGoal->monthly_clicks_goal) }}
                        </p>
                    </div>

                    <!-- Conversion Rate Progress -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <h4 class="text-sm font-medium text-gray-700">Taxa de Conversão</h4>
                            @php
                                $currentConversion = $currentMonthAnalytics && $currentMonthAnalytics->views > 0
                                    ? ($currentMonthAnalytics->clicks / $currentMonthAnalytics->views * 100)
                                    : 0;
                                $conversionProgress = $currentGoal->conversion_rate_goal > 0
                                    ? min(100, $currentConversion / $currentGoal->conversion_rate_goal * 100)
                                    : 0;
                            @endphp
                            <span class="text-sm text-gray-600">{{ number_format($conversionProgress, 1) }}%</span>
                        </div>
                        <div class="relative h-2 bg-yellow-100 rounded-full overflow-hidden">
                            <div class="absolute top-0 left-0 h-full bg-yellow-600 transition-all duration-500"
                                 style="width: {{ $conversionProgress }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600">
                            {{ number_format($currentConversion, 1) }}% de {{ number_format($currentGoal->conversion_rate_goal, 1) }}%
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Goals History -->
            @if($previousGoals->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    Histórico de Metas
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Visualizações</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Cliques</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Taxa de Conversão</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($previousGoals as $goal)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ Carbon\Carbon::createFromDate($goal->year, $goal->month, 1)->format('F/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ number_format($goal->monthly_views_goal) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ number_format($goal->monthly_clicks_goal) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ number_format($goal->conversion_rate_goal, 1) }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Adicione os estilos necessários -->
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        .animate-shake {
            animation: shake 0.3s ease-in-out;
        }
    </style>
</x-app-layout>