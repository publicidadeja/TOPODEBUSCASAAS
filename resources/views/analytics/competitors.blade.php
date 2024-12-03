<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Análise de Concorrentes
            </h2>
            <x-business-selector :businesses="$businesses" :selected="$selectedBusiness" route="analytics.competitors" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Market Share Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Participação no Mercado</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Views Market Share -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500">Visualizações</h4>
                            <div class="mt-2">
                                <canvas id="viewsShare"></canvas>
                            </div>
                        </div>

                        <!-- Clicks Market Share -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500">Cliques</h4>
                            <div class="mt-2">
                                <canvas id="clicksShare"></canvas>
                            </div>
                        </div>

                        <!-- Calls Market Share -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500">Chamadas</h4>
                            <div class="mt-2">
                                <canvas id="callsShare"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Comparison -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Comparação de Performance</h3>
                    <canvas id="performanceComparison"></canvas>
                </div>
            </div>

            <!-- Detailed Metrics Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Métricas Detalhadas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Negócio
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Visualizações
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliques
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Chamadas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Taxa de Conversão
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tendência
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Main Business Row -->
                                <tr class="bg-blue-50">
                                <td class="px-6 py-4 whitespace-nowrap">
    <div class="text-sm text-gray-900">{{ number_format($mainBusinessData['total_views'] ?? 0) }}</div>
    <div class="text-xs text-gray-500">
        Média: {{ number_format($mainBusinessData['avg_views'] ?? 0, 1) }}/dia
        <span class="ml-1 {{ ($mainBusinessData['trend']['views'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $mainBusinessData['trend']['views'] ?? 0 }}%
        </span>
    </div>
</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($mainBusinessData['total_views']) }}</div>
                                        <div class="text-xs text-gray-500">
                                            Média: {{ number_format($mainBusinessData['avg_views'], 1) }}/dia
                                            <span class="ml-1 {{ $mainBusinessData['trend']['views'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $mainBusinessData['trend']['views'] }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($mainBusinessData['total_clicks']) }}</div>
                                        <div class="text-xs text-gray-500">
                                            Média: {{ number_format($mainBusinessData['avg_clicks'], 1) }}/dia
                                            <span class="ml-1 {{ $mainBusinessData['trend']['clicks'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $mainBusinessData['trend']['clicks'] }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($mainBusinessData['total_calls']) }}</div>
                                        <div class="text-xs text-gray-500">
                                            Média: {{ number_format($mainBusinessData['avg_calls'], 1) }}/dia
                                            <span class="ml-1 {{ $mainBusinessData['trend']['calls'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $mainBusinessData['trend']['calls'] }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $mainBusinessData['conversion_rate'] }}%</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($mainBusinessData['trend']['views'] > 0)
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Competitor Rows -->
                                @foreach($competitorsData as $id => $competitor)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $competitor['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $competitor['url'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ number_format($competitor['total_views']) }}</div>
                                            <div class="text-xs text-gray-500">
                                                Média: {{ number_format($competitor['avg_views'], 1) }}/dia
                                                <span class="ml-1 {{ $competitor['trend']['views'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $competitor['trend']['views'] }}%
                                                </span>
                                            </div>
                                        </td>
                                        <!-- Similar cells for other metrics -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Device Distribution -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Distribuição por Dispositivos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <canvas id="deviceDistribution"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights Section -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">Insights</h3>
        <div class="space-y-2">
            @foreach($competitorInsights as $insight)
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                {{ $insight }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Análise Gemini -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Análise Inteligente (Gemini)</h3>
            <button 
                onclick="updateGeminiAnalysis()"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Atualizar Análise
            </button>
        </div>

        <!-- Loading Spinner -->
        <div id="geminiLoading" class="hidden">
            <div class="flex justify-center items-center p-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="ml-2">Gerando análise...</span>
            </div>
        </div>

        <!-- Conteúdo da Análise -->
        <div id="geminiContent" class="space-y-4">
            <!-- Visão Geral do Mercado -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-700 mb-2">Visão Geral do Mercado</h4>
                <p id="marketOverview" class="text-gray-600">
                    {{ $geminiAnalysis['market_overview'] ?? 'Clique em Atualizar Análise para gerar insights sobre o mercado.' }}
                </p>
            </div>

            <!-- Análise de Concorrentes -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-700 mb-2">Análise de Concorrentes</h4>
                <ul id="competitorAnalysis" class="list-disc list-inside text-gray-600">
                    @if(isset($geminiAnalysis['competitor_insights']))
                        @foreach($geminiAnalysis['competitor_insights'] as $insight)
                            <li>{{ $insight }}</li>
                        @endforeach
                    @else
                        <li>Atualize para ver análise dos concorrentes.</li>
                    @endif
                </ul>
            </div>

            <!-- Recomendações -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-700 mb-2">Recomendações Estratégicas</h4>
                <ul id="recommendations" class="list-disc list-inside text-gray-600">
                    @if(isset($geminiAnalysis['recommendations']))
                        @foreach($geminiAnalysis['recommendations'] as $recommendation)
                            <li>{{ $recommendation }}</li>
                        @endforeach
                    @else
                        <li>Atualize para ver recomendações personalizadas.</li>
                    @endif
                </ul>
            </div>

            <!-- Data da Última Atualização -->
            <div class="text-sm text-gray-500 mt-4">
                Última atualização: 
                <span id="lastUpdate">
                    {{ isset($geminiAnalysis['updated_at']) ? \Carbon\Carbon::parse($geminiAnalysis['updated_at'])->format('d/m/Y H:i') : 'Nunca' }}
                </span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateGeminiAnalysis() {
    // Mostra loading
    document.getElementById('geminiLoading').classList.remove('hidden');
    document.getElementById('geminiContent').classList.add('opacity-50');

    // Faz a requisição
    fetch(`/analytics/update-gemini-analysis/{{ $business->id }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.message || 'Erro ao atualizar análise');
        }

        // Atualiza o conteúdo
        document.getElementById('marketOverview').textContent = data.market_overview;
        
        // Atualiza análise de concorrentes
        const competitorList = document.getElementById('competitorAnalysis');
        competitorList.innerHTML = data.competitor_insights.map(insight => 
            `<li>${insight}</li>`
        ).join('');

        // Atualiza recomendações
        const recommendationsList = document.getElementById('recommendations');
        recommendationsList.innerHTML = data.recommendations.map(rec => 
            `<li>${rec}</li>`
        ).join('');

        // Atualiza timestamp
        document.getElementById('lastUpdate').textContent = new Date(data.updated_at).toLocaleString();

        // Mostra mensagem de sucesso
        Swal.fire({
            title: 'Sucesso!',
            text: 'Análise atualizada com sucesso',
            icon: 'success'
        });
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro',
            text: error.message || 'Erro ao atualizar análise. Tente novamente.',
            icon: 'error'
        });
    })
    .finally(() => {
        // Esconde loading
        document.getElementById('geminiLoading').classList.add('hidden');
        document.getElementById('geminiContent').classList.remove('opacity-50');
    });
}
</script>
@endpush

    @push('scripts')
    <script>
        // Dados para os gráficos
        const marketShareData = {
            views: {
                labels: ['{{ $selectedBusiness->name }}', 'Concorrentes'],
                data: [{{ $mainBusinessData['market_share']['views'] }}, {{ 100 - $mainBusinessData['market_share']['views'] }}]
            },
            clicks: {
                labels: ['{{ $selectedBusiness->name }}', 'Concorrentes'],
                data: [{{ $mainBusinessData['market_share']['clicks'] }}, {{ 100 - $mainBusinessData['market_share']['clicks'] }}]
            },
            calls: {
                labels: ['{{ $selectedBusiness->name }}', 'Concorrentes'],
                data: [{{ $mainBusinessData['market_share']['calls'] }}, {{ 100 - $mainBusinessData['market_share']['calls'] }}]
            }
        };

        // Configuração dos gráficos de Market Share
        ['views', 'clicks', 'calls'].forEach(metric => {
            new Chart(document.getElementById(`${metric}Share`).getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: marketShareData[metric].labels,
                    datasets: [{
                        data: marketShareData[metric].data,
                        backgroundColor: ['#3B82F6', '#E5E7EB']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });

        // Performance Comparison Chart
        new Chart(document.getElementById('performanceComparison').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Visualizações', 'Cliques', 'Chamadas'],
                datasets: [{
                    label: '{{ $selectedBusiness->name }}',
                    data: [
                        {{ $mainBusinessData['total_views'] }},
                        {{ $mainBusinessData['total_clicks'] }},
                        {{ $mainBusinessData['total_calls'] }}
                    ],
                    backgroundColor: '#3B82F6'
                },
                @foreach($competitorsData as $competitor)
                {
                    label: '{{ $competitor['name'] }}',
                    data: [
                        {{ $competitor['total_views'] }},
                        {{ $competitor['total_clicks'] }},
                        {{ $competitor['total_calls'] }}
                    ],
                    backgroundColor: '#' + Math.floor(Math.random()*16777215).toString(16)
                },
                @endforeach
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Device Distribution Chart
        new Chart(document.getElementById('deviceDistribution').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    label: '{{ $selectedBusiness->name }}',
                    data: [
                        {{ $mainBusinessData['devices']['desktop'] ?? 0 }},
                        {{ $mainBusinessData['devices']['mobile'] ?? 0 }},
                        {{ $mainBusinessData['devices']['tablet'] ?? 0 }}
                    ],
                    backgroundColor: '#3B82F6'
                },
                @foreach($competitorsData as $competitor)
                {
                    label: '{{ $competitor['name'] }}',
                    data: [
                        {{ $competitor['devices']['desktop'] ?? 0 }},
                        {{ $competitor['devices']['mobile'] ?? 0 }},
                        {{ $competitor['devices']['tablet'] ?? 0 }}
                    ],
                    backgroundColor: '#' + Math.floor(Math.random()*16777215).toString(16)
                },
                @endforeach
                ]
            },
            options: {
                responsive: true,
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
    </script>
    @endpush
</x-app-layout>