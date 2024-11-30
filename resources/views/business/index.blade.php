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
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="text-center">
                            <h3 class="text-lg font-medium mb-4">Nenhum negócio cadastrado</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">Comece importando seus negócios do Google ou cadastre manualmente!</p>
                            <div class="space-x-4">
                                <a href="{{ route('google.auth') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                                    </svg>
                                    Importar do Google
                                </a>
                                <a href="{{ route('business.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Cadastrar Manualmente
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($businesses as $business)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-medium mb-2">{{ $business->name }}</h3>
                                        <p class="text-gray-500 dark:text-gray-400">{{ $business->segment }}</p>
                                    </div>
                                    @if($business->google_business_id)
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Google</span>
                                    @endif
                                </div>
                                
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
                                    @if($business->description)
                                        <p class="text-sm">
                                            <span class="font-medium">Descrição:</span>
                                            {{ $business->description }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex justify-between items-center">
                                    <a href="{{ route('analytics.dashboard', $business->id) }}" class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Ver Analytics →
                                    </a>
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
</x-app-layout>