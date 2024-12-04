<x-app-layout>
    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
    <style>
        #calendar-container {
            height: 600px;
            margin-top: 20px;
        }
        .fc-event { cursor: pointer; }
        .fc-day:hover { background-color: #f8f9fa; }
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
            @if(!isset($business))
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Você precisa cadastrar seu negócio primeiro para acessar a automação.
                                <a href="{{ route('business.create') }}" class="font-medium underline text-blue-700 hover:text-blue-600">
                                    Clique aqui para cadastrar
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Coluna Esquerda -->
                    <div class="space-y-6">
                        <!-- Criar Novo Post -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="p-6">
                                <h3 class="text-lg font-google-sans mb-4 text-gray-800">Criar Novo Post</h3>
                                <form method="POST" action="{{ route('automation.create-post') }}" class="space-y-4">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                                Tipo de Post
                                            </label>
                                            <select id="type" name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                                <option value="promotion">Promoção</option>
                                                <option value="engagement">Engajamento</option>
                                                <option value="information">Informativo</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="scheduled_for" class="block text-sm font-medium text-gray-700 mb-1">
                                                Agendar para
                                            </label>
                                            <input type="datetime-local" id="scheduled_for" name="scheduled_for" 
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="customPrompt" class="block text-sm font-medium text-gray-700 mb-1">
                                            Prompt Personalizado (opcional)
                                        </label>
                                        <textarea id="customPrompt" name="customPrompt" rows="3" 
                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                            Criar Post
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="mt-4">
    <button id="checkSeasonalEvents" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
        Verificar Eventos Sazonais
    </button>
</div>

@push('scripts')
<script>
document.getElementById('checkSeasonalEvents').addEventListener('click', async () => {
    try {
        const response = await fetch(`/automation/segment-events/${businessId}`);
        const data = await response.json();
        
        if (data.success) {
            // Adicionar eventos ao calendário
            data.seasonal_events.forEach(event => {
                calendar.addEvent({
                    title: event,
                    start: new Date(),
                    allDay: true,
                    className: 'seasonal-event'
                });
            });
        }
    } catch (error) {
        console.error('Erro ao buscar eventos sazonais:', error);
    }
});
</script>
@endpush

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

                        <!-- Posts Agendados -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                            <div class="p-6">
                                <h3 class="text-lg font-google-sans mb-4 text-gray-800">Posts Agendados</h3>
                                @if($scheduledPosts->isEmpty())
                                    <p class="text-gray-600">Nenhum post agendado.</p>
                                @else
                                    <div class="space-y-4">
                                        @foreach($scheduledPosts as $post)
                                            <div class="border rounded-lg p-4 hover:shadow-sm transition">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="font-medium text-gray-800">{{ $post->title }}</h4>
                                                        <p class="text-sm text-gray-600">
                                                            Agendado para: {{ $post->scheduled_for->format('d/m/Y H:i') }}
                                                        </p>
                                                        <p class="mt-2 text-gray-700">{{ $post->content }}</p>
                                                    </div>
                                                    <span class="px-2 py-1 text-xs rounded-full 
                                                        {{ $post->type === 'promotion' ? 'bg-green-100 text-green-800' : 
                                                           ($post->type === 'engagement' ? 'bg-blue-100 text-blue-800' : 
                                                           'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst($post->type) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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
                        <input type="text" id="eventTitle" name="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea id="eventDescription" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select id="eventType" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-200">
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

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
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
                                <p class="text-gray-600 mb-2">${suggestion.message}</p>
                                <button onclick="addToCalendar('${suggestion.title}', '${suggestion.date}', '${suggestion.type}')" 
                                        class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                    Adicionar ao Calendário
                                </button>
                            </div>
                        `;
                    });
                });
        }

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
                eventClick: function(info) {
                    // Implementar edição do evento
                    alert('Evento: ' + info.event.title);
                }
            });
            
            calendar.render();
            loadSuggestions();

            // Event Listeners
            document.getElementById('closeModal').addEventListener('click', closeModal);
            
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

        function refreshSuggestions() {
            loadSuggestions();
            showNotification('Sugestões atualizadas!');
        }
    </script>
    @endpush
</x-app-layout>