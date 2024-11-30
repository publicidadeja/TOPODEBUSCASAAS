<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Cadastrar Novo Negócio
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('business.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="name" value="Nome do Negócio" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="segment" value="Segmento" />
                            <x-text-input id="segment" name="segment" type="text" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('segment')" />
                        </div>

                        <div>
                            <x-input-label for="address" value="Endereço" />
                            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('address')" />
                        </div>

                        <div>
                            <x-input-label for="phone" value="Telefone" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div>
                            <x-input-label for="website" value="Website" />
                            <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" />
                            <x-input-error class="mt-2" :messages="$errors->get('website')" />
                        </div>

                        <div>
                            <x-input-label for="description" value="Descrição" />
                            <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm" rows="3"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                Cadastrar Negócio
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>