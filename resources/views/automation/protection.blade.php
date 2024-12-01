
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Proteção Automática') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status da Proteção -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Status da Proteção</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="protection-status">
                        <!-- Carregado via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Análise Competitiva -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Análise Competitiva</h3>
                    <div class="space-y-6" id="competitive-analysis">
                        <!-- Carregado via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Carregar status da proteção
        function loadProtectionStatus() {
            fetch('/automation/protection-status')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('protection-status');
                    container.innerHTML = `
                        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                            <h4 class="font-semibold">Monitoramento 24/7</h4>
                            <p class="text-sm mt-2">Última verificação: ${data.monitoring.last_check}</p>
                            <p class="text-sm">Mudanças detectadas: ${data.monitoring.changes_detected.last_24h}</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                            <h4 class="font-semibold">Backup Automático</h4>
                            <p class="text-sm mt-2">Último backup: ${data.backup.last_backup}</p>
                            <p class="text-sm">Total de backups: ${data.backup.total_backups}</p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
                            <h4 class="font-semibold">Correção Automática</h4>
                            <p class="text-sm mt-2">Correções hoje: ${data.correction.corrections_made.last_24h}</p>
                            <p class="text-sm">Total de correções: ${data.correction.corrections_made.total}</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                            <h4 class="font-semibold">Proteção contra Sabotagem</h4>
                            <p class="text-sm mt-2">Tentativas bloqueadas: ${data.sabotage.attempts_blocked}</p>
                            <p class="text-sm">Nível de risco: ${data.sabotage.risk_level}</p>
                        </div>
                    `;
                });
        }

        // Carregar análise competitiva
        function loadCompetitiveAnalysis() {
            fetch('/automation/competitive-analysis')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('competitive-analysis');
                    container.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Concorrentes -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <h4 class="font-semibold mb-3">Principais Concorrentes</h4>
                                ${data.competitors.main_competitors.map(competitor => `
                                    <div class="mb-3 p-3 bg-white dark:bg-gray-600 rounded">
                                        <p class="font-medium">${competitor.name}</p>
                                        <p class="text-sm">Ponto forte: ${competitor.strength}</p>
                                        <p class="text-sm">Ponto fraco: ${competitor.weakness}</p>
                                    </div>
                                `).join('')}
                            </div>

                            <!-- Oportunidades -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <h4 class="font-semibold mb-3">Oportunidades de Mercado</h4>
                                <div class="space-y-3">
                                    ${data.market_opportunities.high_demand_services.map(service => `
                                        <div class="flex justify-between items-center">
                                            <span>${service.service}</span>
                                            <span class="text-sm bg-green-100 dark:bg-green-800 px-2 py-1 rounded">
                                                ${service.search_volume} buscas
                                            </span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>

                        <!-- Keywords -->
                        <div class="mt-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-semibold mb-3">Palavras-chave Faltantes</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                ${data.keyword_gaps.missing_keywords.map(keyword => `
                                    <div class="bg-white dark:bg-gray-600 p-3 rounded">
                                        <p class="font-medium">${keyword.keyword}</p>
                                        <p class="text-sm">${keyword.monthly_searches} buscas/mês</p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                });
        }

        // Carregar dados quando a página carregar
        document.addEventListener('DOMContentLoaded', () => {
            loadProtectionStatus();
            loadCompetitiveAnalysis();
        });
    </script>
    @endpush
</x-app-layout>