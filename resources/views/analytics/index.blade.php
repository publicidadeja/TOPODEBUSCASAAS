<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Resumo -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Resumo dos Últimos 30 Dias</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                            <div class="text-blue-600 dark:text-blue-300 text-sm">Visualizações</div>
                            <div class="text-2xl font-bold">{{ $summary['total_views'] }}</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                            <div class="text-green-600 dark:text-green-300 text-sm">Cliques</div>
                            <div class="text-2xl font-bold">{{ $summary['total_clicks'] }}</div>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
                            <div class="text-purple-600 dark:text-purple-300 text-sm">Ligações</div>
                            <div class="text-2xl font-bold">{{ $summary['total_calls'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Palavras-chave Populares -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Palavras-chave Populares</h3>
                    @if(count($summary['popular_keywords']) > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($summary['popular_keywords'] as $keyword => $count)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <div class="text-sm">{{ $keyword }}</div>
                                    <div class="text-lg font-semibold">{{ $count }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Nenhuma palavra-chave registrada ainda.</p>
                    @endif
                </div>
            </div>

            <!-- Gráfico de Estatísticas Diárias -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Estatísticas Diárias</h3>
                    <div class="h-64">
                        <!-- Aqui você pode adicionar um gráfico usando Chart.js ou outra biblioteca -->
                        <canvas id="dailyStatsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('dailyStatsChart').getContext('2d');
        const stats = @json($summary['daily_stats']);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: stats.map(stat => stat.date),
                datasets: [
                    {
                        label: 'Visualizações',
                        data: stats.map(stat => stat.views),
                        borderColor: 'rgb(59, 130, 246)',
                        tension: 0.1
                    },
                    {
                        label: 'Cliques',
                        data: stats.map(stat => stat.clicks),
                        borderColor: 'rgb(34, 197, 94)',
                        tension: 0.1
                    },
                    {
                        label: 'Ligações',
                        data: stats.map(stat => stat.calls),
                        borderColor: 'rgb(168, 85, 247)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>