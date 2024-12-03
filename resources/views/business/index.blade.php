<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-google-sans text-xl text-gray-800">
                {{ __('Meus Negócios') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('google.auth') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white rounded-md text-sm text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2 text-google-blue" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                    </svg>
                    Importar do Google
                </a>
                <a href="{{ route('business.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-google-blue text-white rounded-md text-sm hover:bg-google-blue/90 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Novo Negócio
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-google-green/10 border border-google-green/20 text-google-green rounded-lg" role="alert">
                    <span class="font-google-sans">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-google-red/10 border border-google-red/20 text-google-red rounded-lg" role="alert">
                    <span class="font-google-sans">{{ session('error') }}</span>
                </div>
            @endif

            @if($businesses->isEmpty())
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="mt-2 text-lg font-google-sans text-gray-900">Nenhum negócio cadastrado</h3>
                        <p class="mt-1 text-gray-500">Comece adicionando seu primeiro negócio ou importe do Google.</p>
                        <div class="mt-6 flex justify-center space-x-4">
                            <a href="{{ route('business.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-google-blue text-white rounded-md text-sm hover:bg-google-blue/90 transition-colors">
                                Adicionar Negócio
                            </a>
                            <a href="{{ route('google.auth') }}" 
                               class="inline-flex items-center px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md text-sm hover:bg-gray-50 transition-colors">
                                Importar do Google
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Insights e Automações -->
                <div class="mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-google-sans text-gray-900 mb-4">Insights e Automações</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Calendário Inteligente -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-google-sans text-gray-900 mb-3">Calendário Inteligente</h4>
                                <div class="space-y-2">
                                    @foreach($calendarSuggestions ?? [] as $suggestion)
                                    <div class="flex items-center justify-between bg-white p-3 rounded-lg">
                                        <span class="text-sm text-gray-600">{{ $suggestion->message }}</span>
                                        <button class="text-sm text-google-blue hover:text-google-blue/80 transition-colors">
                                            Ativar
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Proteção Automática -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-google-sans text-gray-900 mb-3">Proteção Automática</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Monitoramento 24/7</span>
                                        <x-toggle-switch name="monitoring" :checked="true"/>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Backup Automático</span>
                                        <x-toggle-switch name="backup" :checked="true"/>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Correção Automática</span>
                                        <x-toggle-switch name="autocorrect" :checked="true"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Otimização IA -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-google-sans text-gray-900 mb-3">Otimização por IA</h4>
                                <div class="space-y-2">
                                    @foreach($aiSuggestions ?? [] as $suggestion)
                                    <div class="bg-white p-3 rounded-lg">
                                        <p class="text-sm text-gray-600 mb-2">{{ $suggestion->message }}</p>
                                        <div class="flex justify-end space-x-2">
                                            <button class="text-sm text-google-green hover:text-google-green/80 transition-colors">
                                                Aplicar
                                            </button>
                                            <button class="text-sm text-gray-500 hover:text-gray-600 transition-colors">
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
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-google-sans text-gray-900 mb-1">{{ $business->name }}</h3>
                                    <p class="text-gray-500">{{ $business->segment }}</p>
                                </div>
                                @if($business->google_business_id)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-google-blue/10 text-google-blue">
                                        Google
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-2 mb-4">
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700">Endereço:</span>
                                    <span class="text-gray-600">{{ $business->address }}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700">Telefone:</span>
                                    <span class="text-gray-600">{{ $business->phone }}</span>
                                </p>
                                @if($business->website)
                                    <p class="text-sm">
                                        <span class="font-medium text-gray-700">Website:</span>
                                        <a href="{{ $business->website }}" target="_blank" 
                                           class="text-google-blue hover:text-google-blue/80 transition-colors">
                                            {{ $business->website }}
                                        </a>
                                    </p>
                                @endif
                            </div>

                            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-google-sans text-gray-900 mb-2">Automações Ativas</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Posts Automáticos</span>
                                        <x-toggle-switch name="auto_posts_{{$business->id}}" 
                                                       :checked="$business->settings->auto_posts ?? false"/>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Respostas IA</span>
                                        <x-toggle-switch name="ai_responses_{{$business->id}}" 
                                                       :checked="$business->settings->ai_responses ?? false"/>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="flex space-x-4">
                                    <a href="{{ route('analytics.dashboard', $business->id) }}" 
                                       class="text-sm text-google-blue hover:text-google-blue/80 transition-colors">
                                        Analytics
                                    </a>
                                    <a href="{{ route('business.automation', $business->id) }}" 
                                       class="text-sm text-google-blue hover:text-google-blue/80 transition-colors">
                                        Automações
                                    </a>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('business.edit', $business) }}" 
                                       class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                                        Editar
                                    </a>
                                    <form action="{{ route('business.destroy', $business) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Tem certeza que deseja remover este negócio?')" 
                                                class="px-3 py-1 text-xs font-medium text-google-red bg-google-red/10 rounded-md hover:bg-google-red/20 transition-colors">
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
</x-app-layout>