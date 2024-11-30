<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Metas - {{ $business->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Card de Metas Atuais -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Metas do Mês Atual ({{ now()->format('F/Y') }})
                </h3>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ route('goals.store', $business) }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Meta de Visualizações Mensais
                            </label>
                            <input type="number" name="monthly_views_goal" 
                                value="{{ old('monthly_views_goal', $currentGoal->monthly_views_goal ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('monthly_views_goal')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Meta de Cliques Mensais
                            </label>
                            <input type="number" name="monthly_clicks_goal" 
                                value="{{ old('monthly_clicks_goal', $currentGoal->monthly_clicks_goal ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('monthly_clicks_goal')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Meta de Taxa de Conversão (%)
                            </label>
                            <input type="number" step="0.01" name="conversion_rate_goal" 
                                value="{{ old('conversion_rate_goal', $currentGoal->conversion_rate_goal ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('conversion_rate_goal')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $currentGoal ? 'Atualizar Metas' : 'Definir Metas' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card de Progresso -->
            @if($currentGoal)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Progresso do Mês Atual
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Progresso de Visualizações -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visualizações</h4>
                        <div class="relative pt-1">
                            @php
                                $viewsProgress = $currentGoal->monthly_views_goal > 0 
                                    ? min(100, ($currentMonthAnalytics->views ?? 0) / $currentGoal->monthly_views_goal * 100) 
                                    : 0;
                            @endphp
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                                <div style="width:{{ $viewsProgress }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"></div>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                {{ number_format($currentMonthAnalytics->views ?? 0) }} de {{ number_format($currentGoal->monthly_views_goal) }}
                                ({{ number_format($viewsProgress, 1) }}%)
                            </div>
                        </div>
                    </div>

                    <!-- Progresso de Cliques -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cliques</h4>
                        <div class="relative pt-1">
                            @php
                                $clicksProgress = $currentGoal->monthly_clicks_goal > 0 
                                    ? min(100, ($currentMonthAnalytics->clicks ?? 0) / $currentGoal->monthly_clicks_goal * 100) 
                                    : 0;
                            @endphp
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                                <div style="width:{{ $clicksProgress }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                {{ number_format($currentMonthAnalytics->clicks ?? 0) }} de {{ number_format($currentGoal->monthly_clicks_goal) }}
                                ({{ number_format($clicksProgress, 1) }}%)
                            </div>
                        </div>
                    </div>

                    <!-- Progresso da Taxa de Conversão -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Taxa de Conversão</h4>
                        <div class="relative pt-1">
                            @php
                                $currentConversion = $currentMonthAnalytics && $currentMonthAnalytics->views > 0
                                    ? ($currentMonthAnalytics->clicks / $currentMonthAnalytics->views * 100)
                                    : 0;
                                $conversionProgress = $currentGoal->conversion_rate_goal > 0
                                    ? min(100, $currentConversion / $currentGoal->conversion_rate_goal * 100)
                                    : 0;
                            @endphp
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-yellow-200">
                                <div style="width:{{ $conversionProgress }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-yellow-500"></div>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                {{ number_format($currentConversion, 1) }}% de {{ number_format($currentGoal->conversion_rate_goal, 1) }}%
                                ({{ number_format($conversionProgress, 1) }}%)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Histórico de Metas -->
            @if($previousGoals->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Histórico de Metas
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Período
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Visualizações
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Cliques
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Taxa de Conversão
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach($previousGoals as $goal)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ Carbon\Carbon::createFromDate($goal->year, $goal->month, 1)->format('F/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($goal->monthly_views_goal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($goal->monthly_clicks_goal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
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