
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Assistente IA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Sugestões da IA -->
                    <div id="ai-suggestions" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- As sugestões serão carregadas aqui via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function loadAISuggestions() {
            fetch('/automation/ai-suggestions')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('ai-suggestions');
                    container.innerHTML = '';

                    data.suggestions.forEach(suggestion => {
                        container.innerHTML += `
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
                                <h3 class="text-lg font-semibold mb-2">${suggestion.title}</h3>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">${suggestion.message}</p>
                                <div class="flex space-x-2">
                                    <button onclick="handleSuggestion('${suggestion.type}', '${suggestion.action}', ${JSON.stringify(suggestion.data || {})})"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                        Aplicar
                                    </button>
                                    <button onclick="ignoreSuggestion('${suggestion.type}')"
                                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded">
                                        Ignorar
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                });
        }

        function handleSuggestion(type, action, data) {
            fetch('/automation/handle-suggestion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    suggestion_type: type,
                    action: action,
                    data: data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Sugestão aplicada com sucesso!', 'success');
                    loadAISuggestions(); // Recarrega as sugestões
                } else {
                    showNotification(data.error, 'error');
                }
            });
        }

        // Carrega as sugestões quando a página é carregada
        document.addEventListener('DOMContentLoaded', loadAISuggestions);
    </script>
    @endpush
</x-app-layout>