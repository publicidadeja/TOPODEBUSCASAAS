<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configurações') }} - {{ $business->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Configurações de Notificações</h3>
                    
                    <form action="{{ route('business.settings.update', $business) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Notificações de Visualizações -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium mb-2">Visualizações</h4>
                                <div class="flex items-center">
                                    <input type="checkbox" name="notify_views" id="notify_views" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           {{ $business->settings['notify_views'] ?? false ? 'checked' : '' }}>
                                    <label for="notify_views" class="ml-2">
                                        Notificar quando houver alterações significativas nas visualizações
                                    </label>
                                </div>
                            </div>

                            <!-- Notificações de Cliques -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium mb-2">Cliques</h4>
                                <div class="flex items-center">
                                    <input type="checkbox" name="notify_clicks" id="notify_clicks" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           {{ $business->settings['notify_clicks'] ?? false ? 'checked' : '' }}>
                                    <label for="notify_clicks" class="ml-2">
                                        Notificar quando houver alterações significativas nos cliques
                                    </label>
                                </div>
                            </div>

                            <!-- Notificações de Ligações -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium mb-2">Ligações</h4>
                                <div class="flex items-center">
                                    <input type="checkbox" name="notify_calls" id="notify_calls" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           {{ $business->settings['notify_calls'] ?? false ? 'checked' : '' }}>
                                    <label for="notify_calls" class="ml-2">
                                        Notificar quando houver alterações significativas nas ligações
                                    </label>
                                </div>
                            </div>

                            <!-- Frequência das Notificações -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium mb-2">Frequência das Notificações</h4>
                                <select name="notification_frequency" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option value="daily" {{ ($business->settings['notification_frequency'] ?? '') == 'daily' ? 'selected' : '' }}>
                                        Diariamente
                                    </option>
                                    <option value="weekly" {{ ($business->settings['notification_frequency'] ?? '') == 'weekly' ? 'selected' : '' }}>
                                        Semanalmente
                                    </option>
                                    <option value="monthly" {{ ($business->settings['notification_frequency'] ?? '') == 'monthly' ? 'selected' : '' }}>
                                        Mensalmente
                                    </option>
                                </select>
                            </div>

                            <!-- Limite de Variação para Notificações -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium mb-2">Limite de Variação para Notificações (%)</h4>
                                <input type="number" name="variation_threshold" 
                                       value="{{ $business->settings['variation_threshold'] ?? 10 }}"
                                       min="1" max="100"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <p class="text-sm text-gray-500 mt-1">
                                    Notificar quando houver variação maior que este percentual
                                </p>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="mt-6 flex items-center justify-end">
                            <button type="submit" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Adicione aqui qualquer JavaScript necessário para a página
    </script>
    @endpush
</x-app-layout>