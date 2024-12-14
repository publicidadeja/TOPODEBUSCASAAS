<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-google-sans text-xl text-gray-800">
                {{ __('Analytics') }}
            </h2>
            <x-business-selector :businesses="$businesses" :selected="$selectedBusiness" route="analytics.index" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Resumo -->
            <div class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Visualizações -->
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-gray-600 font-google-sans">Visualizações</h4>
                            <span class="text-sm {{ $summary['trends']['views'] >= 0 ? 'text-google-green' : 'text-google-red' }}">
                                {{ $summary['trends']['views'] }}%
                            </span>
                        </div>
                        <p class="text-3xl font-google-sans">{{ number_format($summary['total_views']) }}</p>
                        <span class="text-sm text-gray-500">vs. período anterior</span>
                    </div>

                    <!-- Cliques -->
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-gray-600 font-google-sans">Cliques</h4>
                            <span class="text-sm {{ $summary['trends']['clicks'] >= 0 ? 'text-google-green' : 'text-google-red' }}">
                                {{ $summary['trends']['clicks'] }}%
                            </span>
                        </div>
                        <p class="text-3xl font-google-sans">{{ number_format($summary['total_clicks']) }}</p>
                        <span class="text-sm text-gray-500">vs. período anterior</span>
                    </div>

                    <!-- Ligações -->
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-gray-600 font-google-sans">Ligações</h4>
                            <span class="text-sm {{ $summary['trends']['calls'] >= 0 ? 'text-google-green' : 'text-google-red' }}">
                                {{ $summary['trends']['calls'] }}%
                            </span>
                        </div>
                        <p class="text-3xl font-google-sans">{{ number_format($summary['total_calls']) }}</p>
                        <span class="text-sm text-gray-500">vs. período anterior</span>
                    </div>
                </div>
            </div>

            <!-- Palavras-chave Populares -->
            <div class="card mb-8">
                <h3 class="text-lg font-google-sans mb-4">Palavras-chave Populares</h3>
                @if(count($summary['popular_keywords']) > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($summary['popular_keywords'] as $keyword => $count)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 font-google-sans">{{ $keyword }}</div>
                                <div class="text-lg font-google-sans mt-1">{{ $count }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 font-google-sans">Nenhuma palavra-chave registrada ainda.</p>
                @endif
            </div>

            <!-- Gráfico de Estatísticas -->
            <div class="card">
                <h3 class="text-lg font-google-sans mb-4">Estatísticas Diárias</h3>
                <div class="h-[400px]">
                    <canvas id="dailyStatsChart"></canvas>
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
                        borderColor: '#4285F4',
                        backgroundColor: 'rgba(66, 133, 244, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Cliques',
                        data: stats.map(stat => stat.clicks),
                        borderColor: '#34A853',
                        backgroundColor: 'rgba(52, 168, 83, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Ligações',
                        data: stats.map(stat => stat.calls),
                        borderColor: '#FBBC05',
                        backgroundColor: 'rgba(251, 188, 5, 0.1)',
                        tension: 0.4,
                        fill: true
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
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Google Sans'
                            },
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 2]
                        },
                        ticks: {
                            font: {
                                family: 'Google Sans'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Google Sans'
                            }
                        }
                    }
                }
            }
        });

    </script>
    @endpush
</x-app-layout>