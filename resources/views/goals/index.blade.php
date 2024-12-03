<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-google-sans text-gray-800">
                Metas - {{ $business->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Current Month Goals Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-google-sans text-gray-800 mb-4">
                    Metas do Mês Atual ({{ now()->format('F/Y') }})
                </h3>

                @if(session('success'))
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('goals.store', $business) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Monthly Views Goal -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 font-google-sans">
                                Meta de Visualizações Mensais
                            </label>
                            <input type="number" name="monthly_views_goal" 
                                value="{{ old('monthly_views_goal', $currentGoal->monthly_views_goal ?? '') }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200">
                            @error('monthly_views_goal')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Monthly Clicks Goal -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 font-google-sans">
                                Meta de Cliques Mensais
                            </label>
                            <input type="number" name="monthly_clicks_goal" 
                                value="{{ old('monthly_clicks_goal', $currentGoal->monthly_clicks_goal ?? '') }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200">
                            @error('monthly_clicks_goal')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Conversion Rate Goal -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 font-google-sans">
                                Meta de Taxa de Conversão (%)
                            </label>
                            <input type="number" step="0.01" name="conversion_rate_goal" 
                                value="{{ old('conversion_rate_goal', $currentGoal->conversion_rate_goal ?? '') }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200">
                            @error('conversion_rate_goal')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 font-google-sans">
                            {{ $currentGoal ? 'Atualizar Metas' : 'Definir Metas' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Progress Card -->
            @if($currentGoal)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-google-sans text-gray-800 mb-6">
                    Progresso do Mês Atual
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Views Progress -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <h4 class="text-sm font-medium text-gray-700 font-google-sans">Visualizações</h4>
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
                            <h4 class="text-sm font-medium text-gray-700 font-google-sans">Cliques</h4>
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
                            <h4 class="text-sm font-medium text-gray-700 font-google-sans">Taxa de Conversão</h4>
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-google-sans text-gray-800 mb-4">
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
</x-app-layout>