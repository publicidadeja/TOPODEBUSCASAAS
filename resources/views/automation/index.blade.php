<x-app-layout>
    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
    <style>

@keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fade-out {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(10px); }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }

    .animate-fade-out {
        animation: fade-out 0.3s ease-out;
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

        #calendar-container {
            height: 600px;
            margin-top: 20px;
        }
        .fc-event { cursor: pointer; }
        .fc-day:hover { background-color: #f8f9fa; }
        .seasonal-event { 
            background-color: #FF9800 !important;
            border-color: #F57C00 !important;
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-google-sans text-gray-800">
                {{ __('Automação de Posts') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Coluna Esquerda -->
                <div class="space-y-6">
                    <!-- Calendário -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-google-sans mb-4 text-gray-800">Calendário de Publicações</h3>
                            <div id="calendar-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita -->
                <div class="space-y-6">
                    <!-- Sugestões de Datas -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-google-sans text-gray-800">Sugestões de Datas</h3>
                                <button onclick="refreshSuggestions()" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Atualizar Sugestões
                                </button>
                            </div>
                            <div id="suggestions-container" class="space-y-4">
                                <!-- Sugestões serão carregadas aqui -->
                            </div>
                        </div>
                    </div>

                    <!-- Verificar Eventos Sazonais -->
                    <div class="mt-4">
                        <button id="checkSeasonalEvents" 
                                class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                            Verificar Eventos Sazonais
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eventos do Calendário -->
    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" style="z-index: 1000;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modalTitle">
                    Criar Novo Evento
                </h3>
                <form id="eventForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Título</label>
                        <input type="text" id="eventTitle" name="title" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea id="eventDescription" name="description" rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select id="eventType" name="type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200">
                            <option value="post">Post</option>
                            <option value="promotion">Promoção</option>
                            <option value="event">Evento</option>
                        </select>
                    </div>

                    <input type="hidden" id="eventStartDate" name="start_date">
                    <input type="hidden" id="eventEndDate" name="end_date">
                </form>
                <div class="flex justify-end gap-3 mt-4">
                    <button id="closeModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button id="saveEvent" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.4.0/main.min.js'></script>
    <script>
        let calendar;

        // Função para mostrar notificações
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
            } animate-fade-in`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        // Função para carregar sugestões
        async function loadSuggestions() {
            try {
                const response = await fetch('/automation/calendar-suggestions');
                const data = await response.json();
                const container = document.getElementById('suggestions-container');
                container.innerHTML = '';

                data.suggestions.forEach(suggestion => {
                    container.innerHTML += `
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold">${suggestion.title}</h4>
                            <p class="text-gray-600 mb-2">${suggestion.message}</p>
                            <button onclick="addToCalendar('${suggestion.title}', '${suggestion.date}', '${suggestion.type}')" 
                                    class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                Adicionar ao Calendário
                            </button>
                        </div>
                    `;
                });
            } catch (error) {
                console.error('Erro ao carregar sugestões:', error);
                showNotification('Erro ao carregar sugestões', 'error');
            }
        }

        // Função para atualizar sugestões
        async function refreshSuggestions() {
    const button = document.querySelector('[onclick="refreshSuggestions()"]');
    try {
        // Desabilita o botão e mostra loading
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Atualizando...
        `;

        // Faz a requisição para o endpoint
        const response = await fetch('/automation/calendar-suggestions?force=true', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) {
            throw new Error('Erro na requisição');
        }

        const data = await response.json();
        const container = document.getElementById('suggestions-container');
        
        // Limpa o container atual
        container.innerHTML = '';

        // Adiciona as novas sugestões
        if (data.suggestions && data.suggestions.length > 0) {
            data.suggestions.forEach(suggestion => {
                container.innerHTML += `
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold">${suggestion.title}</h4>
                        <p class="text-gray-600 mb-2">${suggestion.message}</p>
                        <button onclick="addToCalendar('${suggestion.title}', '${suggestion.date}', '${suggestion.type}')" 
                                class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition-colors">
                            Adicionar ao Calendário
                        </button>
                    </div>
                `;
            });
            showNotification('Sugestões atualizadas com sucesso!', 'success');
        } else {
            container.innerHTML = `
                <div class="text-center py-4 text-gray-600">
                    Nenhuma sugestão disponível no momento.
                </div>
            `;
            showNotification('Nenhuma nova sugestão encontrada', 'info');
        }

    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro ao atualizar sugestões. Tente novamente.', 'error');
    } finally {
        // Restaura o botão ao estado original
        button.disabled = false;
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Atualizar Sugestões
        `;
    }
}

// Função auxiliar para mostrar notificações
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
    } animate-fade-in z-50`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Remove a notificação após 3 segundos
    setTimeout(() => {
        notification.classList.add('animate-fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Função para adicionar evento ao calendário
function addToCalendar(title, date, type) {
    try {
        calendar.addEvent({
            title: title,
            start: date,
            allDay: true,
            extendedProps: {
                type: type
            }
        });
        showNotification('Evento adicionado ao calendário!', 'success');
    } catch (error) {
        console.error('Erro ao adicionar evento:', error);
        showNotification('Erro ao adicionar evento ao calendário', 'error');
    }
}

        // Função para verificar eventos sazonais
        async function checkSeasonalEvents() {
            const button = document.getElementById('checkSeasonalEvents');
            button.disabled = true;
            button.innerHTML = 'Verificando...';

            try {
                const businessId = document.querySelector('meta[name="business-id"]').content;
                const response = await fetch(`/automation/segment-events/${businessId}`);
                const data = await response.json();

                if (data.success && data.seasonal_events) {
                    data.seasonal_events.forEach(event => {
                        calendar.addEvent({
                            title: event.title,
                            start: event.date,
                            allDay: true,
                            className: 'seasonal-event',
                            color: '#FF9800',
                            extendedProps: {
                                type: 'seasonal',
                                description: event.description
                            }
                        });
                    });
                    showNotification(`${data.seasonal_events.length} eventos sazonais encontrados!`, 'success');
                } else {
                    showNotification('Nenhum evento sazonal encontrado para o período.', 'info');
                }
            } catch (error) {
                console.error('Erro:', error);
                showNotification('Erro ao verificar eventos sazonais', 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = 'Verificar Eventos Sazonais';
            }
        }

        // Função para adicionar evento ao calendário
        function addToCalendar(title, date, type) {
            calendar.addEvent({
                title: title,
                start: date,
                allDay: true,
                extendedProps: {
                    type: type
                }
            });
            showNotification('Evento adicionado ao calendário!');
        }

        // Função para abrir modal
        function openModal(info) {
            const modal = document.getElementById('eventModal');
            const startDateInput = document.getElementById('eventStartDate');
            const endDateInput = document.getElementById('eventEndDate');
            
            document.getElementById('eventForm').reset();
            
            startDateInput.value = info.startStr;
            endDateInput.value = info.endStr || info.startStr;
            
            modal.classList.remove('hidden');
        }

        // Função para fechar modal
        function closeModal() {
            document.getElementById('eventModal').classList.add('hidden');
        }

        // Função para manipular clique em evento
        function handleEventClick(info) {
            const event = info.event;
            document.getElementById('eventTitle').value = event.title;
            document.getElementById('eventDescription').value = event.extendedProps.description || '';
            document.getElementById('eventType').value = event.extendedProps.type || 'post';
            document.getElementById('eventStartDate').value = event.startStr;
            document.getElementById('eventEndDate').value = event.endStr;
            
            openModal({event: event});
        }

        // Função para manipular arrasto de evento
        function handleEventDrop(info) {
            const event = info.event;
            fetch('/automation/calendar-event/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id: event.id,
                    start: event.startStr,
                    end: event.endStr
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Evento atualizado com sucesso!', 'success');
                } else {
                    info.revert();
                    showNotification('Erro ao atualizar evento', 'error');
                }
            })
            .catch(error => {
                info.revert();
                showNotification('Erro ao atualizar evento', 'error');
            });
        }

        // Inicialização do calendário
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar-container');
            calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'timeGrid', 'interaction'],
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '/automation/calendar-events',
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                select: openModal,
                eventClick: handleEventClick,
                eventDrop: handleEventDrop
            });
            
            calendar.render();
            loadSuggestions();

            // Event Listeners
            document.getElementById('closeModal').addEventListener('click', closeModal);
            document.getElementById('checkSeasonalEvents').addEventListener('click', checkSeasonalEvents);
            
            document.getElementById('saveEvent').addEventListener('click', function() {
                const eventData = {
                    title: document.getElementById('eventTitle').value,
                    description: document.getElementById('eventDescription').value,
                    type: document.getElementById('eventType').value,
                    start_date: document.getElementById('eventStartDate').value,
                    end_date: document.getElementById('eventEndDate').value
                };

                if (!eventData.title) {
                    showNotification('O título é obrigatório', 'error');
                    return;
                }

                fetch('/automation/calendar-event', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(eventData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        calendar.addEvent({
                            title: eventData.title,
                            start: eventData.start_date,
                            end: eventData.end_date,
                            extendedProps: {
                                type: eventData.type,
                                description: eventData.description
                            }
                        });
                        showNotification('Evento criado com sucesso!');
                        closeModal();
                    } else {
                        showNotification(data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showNotification('Erro ao criar evento', 'error');
                });
            });

            document.getElementById('eventModal').addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });
        });
    </script>
    @endpush
</x-app-layout>