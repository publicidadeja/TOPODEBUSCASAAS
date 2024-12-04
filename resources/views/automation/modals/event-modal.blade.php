<div id="event-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto transform translate-y-1/2">
        <div class="modal-content py-4 text-left px-6">
            <!-- Header -->
            <div class="flex justify-between items-center pb-3">
                <p class="text-xl font-bold" id="modal-title">Criar Evento</p>
                <button class="modal-close cursor-pointer z-50">
                    <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
                        <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <form id="event-form" class="space-y-4">
                @csrf
                <input type="hidden" id="event-id" name="id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Título</label>
                    <input type="text" id="event-title" name="title" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea id="event-description" name="description" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data Início</label>
                        <input type="datetime-local" id="event-start" name="start" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data Fim</label>
                        <input type="datetime-local" id="event-end" name="end" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Evento</label>
                    <select id="event-type" name="type" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="post">Post</option>
                        <option value="seasonal">Sazonal</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>

                <!-- Footer -->
                <div class="flex justify-end pt-2 space-x-4">
                    <button type="button" 
                            class="modal-close px-4 bg-gray-200 p-3 rounded text-black hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 bg-blue-500 p-3 rounded text-white hover:bg-blue-600">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>