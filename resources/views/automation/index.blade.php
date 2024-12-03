<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-google-sans text-gray-800">
                {{ __('Automação de Posts') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!isset($business))
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Você precisa cadastrar seu negócio primeiro para acessar a automação.
                                <a href="{{ route('business.create') }}" class="font-medium underline text-blue-700 hover:text-blue-600">
                                    Clique aqui para cadastrar
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <!-- Grid Layout for Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Create New Post Card -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="p-6">
                                <h3 class="text-lg font-google-sans mb-4 text-gray-800">Criar Novo Post</h3>
                                <form method="POST" action="{{ route('automation.create-post') }}" class="space-y-4">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                                Tipo de Post
                                            </label>
                                            <select id="type" name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                                <option value="promotion">Promoção</option>
                                                <option value="engagement">Engajamento</option>
                                                <option value="information">Informativo</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="scheduled_for" class="block text-sm font-medium text-gray-700 mb-1">
                                                Agendar para
                                            </label>
                                            <input type="datetime-local" id="scheduled_for" name="scheduled_for" 
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="customPrompt" class="block text-sm font-medium text-gray-700 mb-1">
                                            Prompt Personalizado (opcional)
                                        </label>
                                        <textarea id="customPrompt" name="customPrompt" rows="3" 
                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                            Criar Post
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Suggestions Card -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-google-sans text-gray-800">Sugestões de Melhorias</h3>
                                    <button onclick="refreshSuggestions()" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Atualizar Sugestões
                                    </button>
                                </div>
                                <div id="suggestions-container" class="space-y-4">
                                    <!-- Suggestions will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Scheduled Posts Card -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="p-6">
                                <h3 class="text-lg font-google-sans mb-4 text-gray-800">Posts Agendados</h3>
                                @if($scheduledPosts->isEmpty())
                                    <p class="text-gray-600">Nenhum post agendado.</p>
                                @else
                                    <div class="space-y-4">
                                        @foreach($scheduledPosts as $post)
                                            <div class="border rounded-lg p-4 hover:shadow-sm transition">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="font-medium text-gray-800">{{ $post->title }}</h4>
                                                        <p class="text-sm text-gray-600">
                                                            Agendado para: {{ $post->scheduled_for->format('d/m/Y H:i') }}
                                                        </p>
                                                        <p class="mt-2 text-gray-700">{{ $post->content }}</p>
                                                    </div>
                                                    <span class="px-2 py-1 text-xs rounded-full 
                                                        {{ $post->type === 'promotion' ? 'bg-green-100 text-green-800' : 
                                                           ($post->type === 'engagement' ? 'bg-blue-100 text-blue-800' : 
                                                           'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst($post->type) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Posted Posts Card -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="p-6">
                                <h3 class="text-lg font-google-sans mb-4 text-gray-800">Últimos Posts Publicados</h3>
                                @if($postedPosts->isEmpty())
                                    <p class="text-gray-600">Nenhum post publicado ainda.</p>
                                @else
                                    <div class="space-y-4">
                                        @foreach($postedPosts as $post)
                                            <div class="border rounded-lg p-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="font-medium text-gray-800">{{ $post->title }}</h4>
                                                        <p class="text-sm text-gray-600">
                                                            Publicado em: {{ $post->scheduled_for->format('d/m/Y H:i') }}
                                                        </p>
                                                        <p class="mt-2 text-gray-700">{{ $post->content }}</p>
                                                    </div>
                                                    <span class="px-2 py-1 text-xs rounded-full 
                                                        {{ $post->type === 'promotion' ? 'bg-green-100 text-green-800' : 
                                                           ($post->type === 'engagement' ? 'bg-blue-100 text-blue-800' : 
                                                           'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst($post->type) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        const businessId = {{ $business->id ?? 'null' }};

        function refreshSuggestions() {
            if (!businessId) {
                showNotification('Você precisa cadastrar seu negócio primeiro.', 'error');
                return;
            }
            
            const button = document.querySelector('button[onclick="refreshSuggestions()"]');
            button.disabled = true;
            button.classList.add('opacity-75');
            
            const icon = button.querySelector('svg');
            icon.classList.add('animate-spin');
            
            fetch(`/automation/suggestions/${businessId}?refresh=true`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('suggestions-container');
                    
                    if (!data.suggestions || data.suggestions.length === 0) {
                        container.innerHTML = '<p class="text-gray-500 text-center p-4">Nenhuma sugestão disponível no momento.</p>';
                        return;
                    }
                    
                    container.innerHTML = data.suggestions.map(suggestion => `
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium mb-2">${suggestion.title || 'Sem título'}</h4>
                            <p class="text-sm mb-3">${suggestion.message || suggestion.description || 'Sem descrição'}</p>
                            <div class="flex justify-end space-x-2">
                                <button onclick="applyImprovement('${suggestion.action_type}', ${JSON.stringify(suggestion.action_data)})"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition">
                                    Aplicar Melhoria
                                </button>
                                <button onclick="dismissSuggestion(${suggestion.id})"
                                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300 transition">
                                    Ignorar
                                </button>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showNotification('Erro ao carregar sugestões: ' + error.message, 'error');
                })
                .finally(() => {
                    button.disabled = false;
                    button.classList.remove('opacity-75');
                    icon.classList.remove('animate-spin');
                });
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        function applyImprovement(type, data) {
            fetch(`/automation/apply-improvement/${businessId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ improvement_type: type, data: data })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Melhoria aplicada com sucesso!', 'success');
                    refreshSuggestions();
                } else {
                    showNotification(data.error, 'error');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            refreshSuggestions();
            setInterval(refreshSuggestions, 300000);
        });
    </script>
    @endpush
</x-app-layout>