<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Relatório - {{ $type }} - {{ $selectedBusiness->name }}
            </h2>
            
            <div class="flex items-center space-x-4">
                <!-- Exportar -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        Exportar
                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <a href="{{ route('analytics.export.pdf', ['businessId' => $selectedBusiness->id]) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Exportar PDF
                            </a>
                            <a href="{{ route('analytics.export.excel', ['businessId' => $selectedBusiness->id]) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Exportar Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Seletor de Negócio -->
                <select id="business-selector" class="rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}" {{ $business->id == $selectedBusiness->id ? 'selected' : '' }}>
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Conteúdo do Relatório -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Relatório em Desenvolvimento
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Este recurso está sendo implementado e estará disponível em breve.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Event Listeners
        document.getElementById('business-selector').addEventListener('change', function() {
            window.location.href = `/analytics/report/${type}/${this.value}`;
        });
    </script>
    @endpush
</x-app-layout>