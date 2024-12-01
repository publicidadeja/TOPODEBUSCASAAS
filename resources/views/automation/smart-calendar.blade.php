<x-app-layout>
    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
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

        .notification.success {
            background-color: #4CAF50;
        }

        .notification.error {
            background-color: #f44336;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #calendar-container {
            height: 800px;
            margin-top: 20px;
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

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.4.0/main.min.js'></script>
    <script>
        // Função para carregar sugestões
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

        document.addEventListener('DOMContentLoaded', function() {
            // Carregar sugestões
            loadSuggestions();

            // Inicializar o calendário
            var calendarEl = document.getElementById('calendar-container');
            var calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: ['dayGrid', 'timeGrid'], // Adicionar timeGrid
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
    select: function(info) {
        handleNewEvent(info);
    }
});
            calendar.render();

            // Função para criar novo evento
            window.handleNewEvent = function(info) {
    // Criar um modal ou form mais elaborado ao invés de usar prompt
    const title = prompt('Digite o título do evento:');
    if (title) {
        const eventData = {
            title: title,
            event_type: 'custom',
            suggestion: 'Evento personalizado',
            start_date: info.startStr,
            end_date: info.endStr || info.startStr // Caso não tenha data final
        };

        // Adicionar o token CSRF
        const token = document.querySelector('meta[name="csrf-token"]').content;

        fetch('/automation/calendar-event', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(eventData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao criar evento');
            }
            return response.json();
        })
        .then(data => {
            // Adicionar o evento ao calendário
            calendar.addEvent({
                id: data.id, // Assumindo que o backend retorna o ID do evento
                title: title,
                start: info.startStr,
                end: info.endStr || info.startStr,
                allDay: !info.endStr
            });
            showNotification('Evento criado com sucesso!', 'success');
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao criar evento: ' + error.message, 'error');
        });
    }
}

            // Função para lidar com sugestões
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

            // Função para mostrar notificações
            window.showNotification = function(message, type) {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        });
    </script>
    @endpush
</x-app-layout>