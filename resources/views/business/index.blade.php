<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Meus Negócios
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('google.auth') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                    </svg>
                    Importar do Google
                </a>
                <a href="{{ route('business.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Novo Negócio
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($businesses->isEmpty())
                <!-- Seção de negócios vazios - mantida como está -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- ... código existente ... -->
                </div>
            @else
                <!-- Nova seção de Insights e Automações -->
                <div class="mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">Insights e Automações</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Calendário Inteligente -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <h4 class="font-medium mb-3">Calendário Inteligente</h4>
                                <div class="space-y-2">
                                    @foreach($calendarSuggestions ?? [] as $suggestion)
                                    <div class="flex items-center justify-between bg-white dark:bg-gray-600 p-3 rounded">
                                        <span class="text-sm">{{ $suggestion->message }}</span>
                                        <button class="text-indigo-600 text-sm hover:underline">
                                            Ativar
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Proteção Automática -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <h4 class="font-medium mb-3">Proteção Automática</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm">Monitoramento 24/7</span>
                                        <x-toggle-switch 
    name="monitoring" 
    :checked="true"
    label="Monitoramento 24/7"
/>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm">Backup Automático</span>
                                        <x-toggle-switch name="backup" :checked="true"/>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm">Correção Automática</span>
                                        <x-toggle-switch name="autocorrect" :checked="true"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Otimização IA -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <h4 class="font-medium mb-3">Otimização por IA</h4>
                                <div class="space-y-2">
                                    @foreach($aiSuggestions ?? [] as $suggestion)
                                    <div class="bg-white dark:bg-gray-600 p-3 rounded">
                                        <p class="text-sm mb-2">{{ $suggestion->message }}</p>
                                        <div class="flex justify-end space-x-2">
                                            <button class="text-green-600 text-sm hover:underline">
                                                Aplicar
                                            </button>
                                            <button class="text-gray-500 text-sm hover:underline">
                                                Ignorar
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Negócios -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($businesses as $business)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <!-- Cabeçalho do Card -->
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-medium mb-2">{{ $business->name }}</h3>
                                        <p class="text-gray-500 dark:text-gray-400">{{ $business->segment }}</p>
                                    </div>
                                    @if($business->google_business_id)
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Google</span>
                                    @endif
                                </div>

                                <!-- Informações do Negócio -->
                                <div class="space-y-2 mb-4">
                                    <p class="text-sm">
                                        <span class="font-medium">Endereço:</span> {{ $business->address }}
                                    </p>
                                    <p class="text-sm">
                                        <span class="font-medium">Telefone:</span> {{ $business->phone }}
                                    </p>
                                    @if($business->website)
                                        <p class="text-sm">
                                            <span class="font-medium">Website:</span>
                                            <a href="{{ $business->website }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ $business->website }}
                                            </a>
                                        </p>
                                    @endif
                                </div>

                                <!-- Automações Específicas -->
                                <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <h4 class="text-sm font-medium mb-2">Automações Ativas</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm">Posts Automáticos</span>
                                            <x-toggle-switch name="auto_posts_{{$business->id}}" :checked="$business->settings->auto_posts ?? false"/>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm">Respostas IA</span>
                                            <x-toggle-switch name="ai_responses_{{$business->id}}" :checked="$business->settings->ai_responses ?? false"/>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ações -->
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('analytics.dashboard', $business->id) }}" class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                            Analytics
                                        </a>
                                        <a href="{{ route('business.automation', $business->id) }}" class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                            Automações
                                        </a>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('business.edit', $business) }}" class="inline-flex items-center px-3 py-1 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Editar
                                        </a>
                                        <form action="{{ route('business.destroy', $business) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Tem certeza que deseja remover este negócio?')" class="inline-flex items-center px-3 py-1 bg-red-100 dark:bg-red-800 border border-transparent rounded-md font-semibold text-xs text-red-700 dark:text-red-200 uppercase tracking-widest hover:bg-red-200 dark:hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Scripts específicos da página -->
    @push('scripts')
    <script>
        // Funções para manipular as automações
        function toggleAutomation(businessId, feature) {
            // Implementar lógica de toggle
        }

        // Funções para manipular sugestões da IA
        function handleAISuggestion(suggestionId, action) {
            // Implementar lógica de sugestões
        }
    </script>
    @endpush
</x-app-layout>