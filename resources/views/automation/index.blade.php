<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Automação de Posts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Criar Novo Post -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Criar Novo Post</h3>
                    <form method="POST" action="{{ route('automation.create-post') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="type" :value="__('Tipo de Post')" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="promotion">Promoção</option>
                                    <option value="engagement">Engajamento</option>
                                    <option value="information">Informativo</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="scheduled_for" :value="__('Agendar para')" />
                                <x-text-input id="scheduled_for" name="scheduled_for" type="datetime-local" class="mt-1 block w-full" required />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="customPrompt" :value="__('Prompt Personalizado (opcional)')" />
                            <textarea id="customPrompt" name="customPrompt" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <x-primary-button>
                                {{ __('Criar Post') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Seção de Sugestões de Melhorias -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Sugestões de Melhorias</h3>
                <div id="suggestions-container" class="space-y-4">
                    <!-- Sugestões serão carregadas aqui via JavaScript -->
                </div>
            </div>
        </div>

        <!-- Seção de Ações Pendentes -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Ações Pendentes</h3>
                <div id="pending-actions" class="space-y-4">
                    <!-- Ações pendentes serão carregadas aqui -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadSuggestions() {
    fetch(`/automation/suggestions/${businessId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('suggestions-container');
            container.innerHTML = data.suggestions.map(suggestion => `
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <h4 class="font-medium mb-2">${suggestion.title}</h4>
                    <p class="text-sm mb-3">${suggestion.message}</p>
                    <div class="flex justify-end space-x-2">
                        <button onclick="applyImprovement('${suggestion.action_type}', ${JSON.stringify(suggestion.action_data)})"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm">
                            Aplicar Melhoria
                        </button>
                        <button onclick="dismissSuggestion(${suggestion.id})"
                                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">
                            Ignorar
                        </button>
                    </div>
                </div>
            `).join('');
        });
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
            loadSuggestions(); // Recarrega as sugestões
        } else {
            showNotification(data.error, 'error');
        }
    });
}

// Carregar sugestões quando a página carregar
document.addEventListener('DOMContentLoaded', loadSuggestions);
</script>
@endpush

            <!-- Posts Agendados -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Posts Agendados</h3>
                    @if($scheduledPosts->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">Nenhum post agendado.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($scheduledPosts as $post)
                                <div class="border dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold">{{ $post->title }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Agendado para: {{ $post->scheduled_for->format('d/m/Y H:i') }}
                                            </p>
                                            <p class="mt-2">{{ $post->content }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($post->type === 'promotion') bg-green-100 text-green-800
                                            @elseif($post->type === 'engagement') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($post->type) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Posts Publicados -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Últimos Posts Publicados</h3>
                    @if($postedPosts->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">Nenhum post publicado ainda.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($postedPosts as $post)
                                <div class="border dark:border-gray-700 rounded-lg p-4 opacity-75">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold">{{ $post->title }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Publicado em: {{ $post->scheduled_for->format('d/m/Y H:i') }}
                                            </p>
                                            <p class="mt-2">{{ $post->content }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($post->type === 'promotion') bg-green-100 text-green-800
                                            @elseif($post->type === 'engagement') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
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
</x-app-layout>