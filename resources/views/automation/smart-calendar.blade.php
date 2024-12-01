<x-app-layout>
    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
    <style>
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            animation: fadeIn 0.3s ease-in;
        }

        .notification.success { background-color: #4CAF50; }
        .notification.error { background-color: #f44336; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #calendar-container {
            height: 800px;
            margin-top: 20px;
        }

        .fc-event { cursor: pointer; }
        .fc-day:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
    </style>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Calendário Inteligente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Seção de Sugestões -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Sugestões para seu Negócio</h3>
                        <div id="suggestions-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- As sugestões serão carregadas aqui via JavaScript -->
                        </div>
                    </div>

                    <!-- Calendário de Eventos -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Eventos Programados</h3>
                        <div id="calendar-container">
                            <!-- O calendário será renderizado aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Criar/Editar Evento -->
    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" style="z-index: 1000;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modalTitle">
                    Criar Novo Evento
                </h3>
                <div class="mt-2">
                    <form id="eventForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" id="eventTitle" name="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea id="eventDescription" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cor do Evento</label>
                            <select id="eventColor" name="color" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="#3788d8">Azul</option>
                                <option value="#28a745">Verde</option>
                                <option value="#dc3545">Vermelho</option>
                                <option value="#ffc107">Amarelo</option>
                                <option value="#6c757d">Cinza</option>
                            </select>
                        </div>

                        <input type="hidden" id="eventStartDate" name="start_date">
                        <input type="hidden" id="eventEndDate" name="end_date">
                    </form>
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button id="closeModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button id="saveEvent" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
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

        // Funções auxiliares
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.remove(), 3000);
        }

        function loadSuggestions() {
            fetch('/automation/calendar-suggestions')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('suggestions-container');
                    container.innerHTML = '';

                    data.suggestions.forEach(suggestion => {
                        container.innerHTML += `
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">${suggestion.title}</h4>
                                <p class="text-gray-600">${suggestion.message}</p>
                                <button onclick="handleSuggestion('${suggestion.type}')" 
                                        class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    Aplicar Sugestão
                                </button>
                            </div>
                        `;
                    });
                });
        }

        // Funções do Modal
        function openModal(info) {
            const modal = document.getElementById('eventModal');
            const startDateInput = document.getElementById('eventStartDate');
            const endDateInput = document.getElementById('eventEndDate');
            
            document.getElementById('eventForm').reset();
            
            startDateInput.value = info.startStr;
            endDateInput.value = info.endStr || info.startStr;
            
            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('eventModal').classList.add('hidden');
        }

        // Handlers
        window.handleNewEvent = function(info) {
            openModal(info);
        }

        window.handleSuggestion = function(type) {
            fetch('/automation/calendar-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    event_type: type,
                    title: 'Evento Sugerido',
                    suggestion: 'Sugestão automática',
                    start_date: new Date().toISOString(),
                    end_date: new Date(Date.now() + 86400000).toISOString()
                })
            })
            .then(response => response.json())
            .then(data => {
                calendar.refetchEvents();
                showNotification('Sugestão aplicada com sucesso!', 'success');
            })
            .catch(error => {
                showNotification('Erro ao aplicar sugestão', 'error');
            });
        }

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            loadSuggestions();

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
                select: handleNewEvent,
                eventClick: function(info) {
                    alert('Evento: ' + info.event.title);
                },
                eventDrop: function(info) {
                    alert('Evento movido!');
                },
                eventResize: function(info) {
                    alert('Evento redimensionado!');
                }
            });
            
            calendar.render();

            // Event Listeners do Modal
            document.getElementById('closeModal').addEventListener('click', closeModal);
            
            document.getElementById('saveEvent').addEventListener('click', function() {
    const title = document.getElementById('eventTitle').value;
    const description = document.getElementById('eventDescription').value;
    const color = document.getElementById('eventColor').value;
    const startDate = document.getElementById('eventStartDate').value;
    const endDate = document.getElementById('eventEndDate').value;

    if (!title) {
        showNotification('O título é obrigatório', 'error');
        return;
    }

    const eventData = {
        title: title,
        description: description,
        color: color,
        event_type: 'custom',
        start_date: startDate,
        end_date: endDate
    };

    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/automation/calendar-event', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json' // Add this line to ensure JSON response
        },
        body: JSON.stringify(eventData)
    })
    .then(response => {
        if (!response.ok) {
            // Read the response as text first
            return response.text().then(text => {
                try {
                    // Try to parse it as JSON
                    return Promise.reject(JSON.parse(text));
                } catch (e) {
                    // If it's not JSON, return a generic error
                    console.error('Server Response:', text);
                    return Promise.reject(new Error('Erro no servidor. Por favor, tente novamente.'));
                }
            });
        }
        return response.json();
    })
    .then(data => {
        calendar.addEvent({
            id: data.id,
            title: title,
            description: description,
            backgroundColor: color,
            borderColor: color,
            start: startDate,
            end: endDate,
            allDay: !endDate || startDate === endDate
        });
        
        closeModal();
        showNotification('Evento criado com sucesso!', 'success');
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification(error.message || 'Erro ao criar evento. Por favor, tente novamente.', 'error');
    });
});
            document.getElementById('eventModal').addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });
        });
    </script>
    @endpush
</x-app-layout>