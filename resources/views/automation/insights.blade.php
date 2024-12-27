<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Insights de IA - {{ $business->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Performance Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Análise de Performance</h3>
                        <div id="performance-insights"></div>
                    </div>

                    <!-- Content Suggestions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Sugestões de Conteúdo</h3>
                        <div id="content-suggestions"></div>
                    </div>

                    <!-- Competitor Analysis -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Análise de Concorrentes</h3>
                        <div id="competitor-analysis"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Adicione esta seção no grid principal -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Controles de Automação -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Automações do Google Meu Negócio</h3>
        
        <!-- Toggle Posts Automáticos -->
        <div class="mb-4">
            <label class="flex items-center space-x-3">
                <input type="checkbox" 
                       class="form-checkbox h-5 w-5 text-blue-600"
                       onchange="toggleAutomation('posts')"
                       @if($business->settings['auto_posts'] ?? false) checked @endif>
                <span>Posts Automáticos</span>
            </label>
            <p class="text-sm text-gray-600 mt-1">
                Permite que o sistema crie e publique posts automaticamente
            </p>
        </div>

        <!-- Toggle Calendário Automático -->
        <div class="mb-4">
            <label class="flex items-center space-x-3">
                <input type="checkbox" 
                       class="form-checkbox h-5 w-5 text-blue-600"
                       onchange="toggleAutomation('calendar')"
                       @if($business->settings['auto_calendar'] ?? false) checked @endif>
                <span>Calendário Inteligente</span>
            </label>
            <p class="text-sm text-gray-600 mt-1">
                Preenche automaticamente o calendário com datas importantes
            </p>
        </div>
    </div>

    <!-- Calendário de Eventos -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Calendário de Eventos</h3>
        <div id="calendar"></div>
    </div>
</div>
    @push('scripts')
    <script>
        function loadInsights() {
            fetch(`/automation/insights/${businessId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateInsightsSections(data.insights);
                    }
                })
                .catch(error => console.error('Erro ao carregar insights:', error));
        }

        function updateInsightsSections(insights) {
            // Update Performance Section
            document.getElementById('performance-insights').innerHTML = 
                generatePerformanceHTML(insights.performance);

            // Update Content Suggestions
            document.getElementById('content-suggestions').innerHTML = 
                generateSuggestionsHTML(insights.content);

            // Update Competitor Analysis
            document.getElementById('competitor-analysis').innerHTML = 
                generateCompetitorHTML(insights.competitors);
        }

        // Load insights when page loads
        document.addEventListener('DOMContentLoaded', loadInsights);
    </script>
    @endpush
</x-app-layout>