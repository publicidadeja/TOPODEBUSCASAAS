<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Calendário Inteligente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Seção de Sugestões -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Sugestões para seu Negócio</h3>
                        <div id="suggestions-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- As sugestões serão carregadas aqui via JavaScript -->
                        </div>
                    </div>

                    <!-- Calendário de Eventos -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Eventos Programados</h3>
                        <div id="calendar-container">
                            <!-- O calendário será renderizado aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Função para carregar sugestões
        function loadSuggestions() {
            fetch('/automation/calendar-suggestions')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('suggestions-container');
                    container.innerHTML = '';

                    data.suggestions.forEach(suggestion => {
                        container.innerHTML += `
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">${suggestion.title}</h4>
                                <p class="text-gray-600">${suggestion.message}</p>
                                <button onclick="handleSuggestion('${suggestion.type}')" 
                                        class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    Aplicar Sugestão
                                </button>
                            </div>
                        `;
                    });
                });
        }

        // Carregar sugestões quando a página carregar
        document.addEventListener('DOMContentLoaded', loadSuggestions);
    </script>
    @endpush
</x-app-layout>