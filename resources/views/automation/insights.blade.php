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