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
                        <p class="mt-1 text-gray-500">Comece importando seus negócios do Google Meu Negócio.</p>
                        <div class="mt-6">
                            <a href="{{ route('google.auth') }}" 
                               class="inline-flex items-center px-4 py-2 bg-google-blue text-white rounded-md text-sm hover:bg-google-blue/90 transition-colors">
                                Importar do Google
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($businesses as $business)
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <!-- Imagem do negócio -->
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ $business->cover_photo_url }}" 
                                 alt="{{ $business->name }}" 
                                 class="w-full h-full object-cover">
                        </div>
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-google-sans text-gray-900 mb-1">{{ $business->name }}</h3>
                                    <div class="flex items-center mb-2">
                                        <div class="flex items-center text-google-yellow">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $business->rating ? 'text-google-yellow' : 'text-gray-300' }}" 
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600">{{ $business->rating }} ({{ $business->review_count }} avaliações)</span>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-3">{{ $business->description }}</p>
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm text-gray-600">{{ $business->address }}</p>
                                        <p class="text-sm text-gray-500">{{ $business->city }}, {{ $business->state }} - {{ $business->postal_code }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <p class="text-sm text-gray-600">{{ $business->phone }}</p>
                                </div>

                                @if($business->website)
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                        <a href="{{ $business->website }}" target="_blank" 
                                           class="text-sm text-google-blue hover:text-google-blue/80 transition-colors">
                                            {{ $business->website }}
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="flex justify-between items-center">
                                <a href="{{ route('business.edit', $business) }}" 
                                   class="text-sm text-gray-700 hover:text-gray-900 transition-colors">
                                    Editar
                                </a>
                                <form action="{{ route('business.destroy', $business) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Tem certeza que deseja remover este negócio?')" 
                                            class="text-sm text-google-red hover:text-google-red/80 transition-colors">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>