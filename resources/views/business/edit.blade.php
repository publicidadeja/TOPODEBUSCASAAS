<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-google-sans text-xl text-gray-800">
                Editar Negócio: {{ $business->name }}
            </h2>
            <a href="{{ route('business.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-white rounded-md text-sm text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Card do formulário -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6">
                    <!-- Imagem de capa -->
                    <div class="mb-6">
                        <div class="relative aspect-w-16 aspect-h-9 rounded-lg overflow-hidden bg-gray-100">
                            <img src="{{ $business->cover_photo_url ?? asset('images/default-business-cover.jpg') }}" 
                                 alt="Capa do negócio" 
                                 class="object-cover w-full h-full">
                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                <label for="cover_photo" class="cursor-pointer group">
                                    <div class="p-4 rounded-full bg-white bg-opacity-0 group-hover:bg-opacity-10 transition-all">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <input type="file" id="cover_photo" name="cover_photo" class="hidden" accept="image/*">
                                </label>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 text-center">
                            Clique na imagem para alterar a foto de capa
                        </p>
                    </div>

                    <form method="POST" action="{{ route('business.update', $business) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Informações básicas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" value="Nome do Negócio" />
                                <x-text-input id="name" name="name" type="text" 
                                             class="mt-1 block w-full" 
                                             :value="old('name', $business->name)" 
                                             required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="segment" value="Segmento" />
                                <x-text-input id="segment" name="segment" type="text" 
                                             class="mt-1 block w-full" 
                                             :value="old('segment', $business->segment)" 
                                             required />
                                <x-input-error class="mt-2" :messages="$errors->get('segment')" />
                            </div>
                        </div>

                        <!-- Informações de contato -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="phone" value="Telefone" />
                                <x-text-input id="phone" name="phone" type="text" 
                                             class="mt-1 block w-full" 
                                             :value="old('phone', $business->phone)" 
                                             required />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>

                            <div>
                                <x-input-label for="website" value="Website" />
                                <x-text-input id="website" name="website" type="url" 
                                             class="mt-1 block w-full" 
                                             :value="old('website', $business->website)" />
                                <x-input-error class="mt-2" :messages="$errors->get('website')" />
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div>
                            <x-input-label for="address" value="Endereço" />
                            <x-text-input id="address" name="address" type="text" 
                                         class="mt-1 block w-full" 
                                         :value="old('address', $business->address)" 
                                         required />
                            <x-input-error class="mt-2" :messages="$errors->get('address')" />
                        </div>

                        <!-- Localização -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="latitude" value="Latitude" />
        <x-text-input id="latitude" name="latitude" type="text" 
                     class="mt-1 block w-full" 
                     :value="old('latitude', $business->latitude)" 
                     required />
        <x-input-error class="mt-2" :messages="$errors->get('latitude')" />
    </div>

    <div>
        <x-input-label for="longitude" value="Longitude" />
        <x-text-input id="longitude" name="longitude" type="text" 
                     class="mt-1 block w-full" 
                     :value="old('longitude', $business->longitude)" 
                     required />
        <x-input-error class="mt-2" :messages="$errors->get('longitude')" />
    </div>
</div>

                        <!-- Descrição -->
                        <div>
                            <x-input-label for="description" value="Descrição" />
                            <textarea id="description" name="description" 
                                      class="mt-1 block w-full rounded-md border-gray-300 focus:border-google-blue focus:ring-google-blue shadow-sm" 
                                      rows="4">{{ old('description', $business->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <!-- Botões de ação -->
                        <div class="flex items-center justify-end pt-6 border-t border-gray-100">
                            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                                Cancelar
                            </x-secondary-button>
                            <x-primary-button class="bg-google-blue hover:bg-google-blue/90">
                                Salvar Alterações
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para preview da imagem -->
    <script>
        document.getElementById('cover_photo').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('img').src = e.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</x-app-layout>