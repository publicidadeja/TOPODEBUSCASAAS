<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard de Automação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Métricas de Automação -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <x-metric-card
                    title="Taxa de Sucesso"
                    :value="$metrics['success_rate'] . '%'"
                    :change="$metrics['success_rate_change']"
                />
                <x-metric-card
                    title="Posts Automatizados"
                    :value="$metrics['automated_posts_count']"
                    :change="$metrics['posts_change']"
                />
                <x-metric-card
                    title="Score de Automação"
                    :value="$metrics['automation_score']"
                    :change="$metrics['score_change']"
                />
            </div>

            <!-- Gráficos de Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Performance ao Longo do Tempo</h3>
                    <div id="performance-chart" style="height: 300px;"></div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Distribuição de Ações</h3>
                    <div id="actions-chart" style="height: 300px;"></div>
                </div>
            </div>

            <!-- Lista de Insights -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Últimos Insights</h3>
                <div class="space-y-4">
                    @foreach($insights as $insight)
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-semibold">{{ $insight->title }}</h4>
                            <p class="text-gray-600">{{ $insight->message }}</p>
                            <div class="mt-2 flex items-center space-x-4">
                                <span class="text-sm text-gray-500">
                                    {{ $insight->created_at->diffForHumans() }}
                                </span>
                                <div class="flex items-center space-x-2">
                                    <button onclick="provideFeedback('{{ $insight->id }}', 'helpful')"
                                            class="text-sm text-green-500 hover:text-green-600">
                                        Útil
                                    </button>
                                    <button onclick="provideFeedback('{{ $insight->id }}', 'not_helpful')"
                                            class="text-sm text-red-500 hover:text-red-600">
                                        Não Útil
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Configuração dos gráficos
        const performanceChart = new ApexCharts(
            document.querySelector("#performance-chart"),
            {
                // Configurações do gráfico de performance
            }
        );
        performanceChart.render();

        const actionsChart = new ApexCharts(
            document.querySelector("#actions-chart"),
            {
                // Configurações do gráfico de ações
            }
        );
        actionsChart.render();

        // Função para enviar feedback
        function provideFeedback(insightId, feedbackType) {
            fetch('/automation/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    suggestion_id: insightId,
                    feedback_type: feedbackType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar interface
                }
            });
        }
    </script>
    @endpush
</x-app-layout>