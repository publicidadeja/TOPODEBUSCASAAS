<x-app-layout>
    @push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css' rel='stylesheet' />
    <style>
        .fc-event {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .fc-event:hover {
            transform: scale(1.02);
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        .animate-fade-out {
            animation: fadeOut 0.3s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
    @endpush

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Cabeçalho da Página -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    Automação do Google Meu Negócio
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Gerencie suas automações e mantenha seu negócio atualizado automaticamente
                </p>
            </div>

<!-- Painel de Status -->
<div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Status da Integração -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                @if($business->is_connected)
                    <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @else
                    <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @endif
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">Status da Integração</h3>
                <p class="text-sm text-gray-500">
                    @if($business->is_connected)
                        Google Meu Negócio conectado
                        @if($business->last_sync_at)
                            <div class="mt-1 text-xs text-gray-400">
                                Última sincronização: {{ $business->last_sync_at->diffForHumans() }}
                            </div>
                        @endif
                    @else
                        Não conectado
                        <a href="{{ route('business.settings', $business) }}" 
                           class="text-blue-600 hover:text-blue-800">
                            Configurar integração
                        </a>
                    @endif
                </p>
            </div>
        </div>
    </div>


    <!-- Posts Automáticos -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Posts Automáticos</h3>
                <p class="text-sm text-gray-500">Status: 
                    <span id="autoPostStatus" class="@if($business->automation_settings['auto_posts'] ?? false) text-green-500 @else text-gray-500 @endif">
                        {{ ($business->automation_settings['auto_posts'] ?? false) ? 'Ativo' : 'Inativo' }}
                    </span>
                </p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" 
                       id="autoPostToggle" 
                       class="sr-only peer" 
                       onchange="toggleAutomation('posts', this.checked)"
                       @if($business->automation_settings['auto_posts'] ?? false) checked @endif
                       @if(!$business->is_connected) disabled @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></div>
            </label>
        </div>
    </div>

    <!-- Calendário Inteligente -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Calendário Inteligente</h3>
                <p class="text-sm text-gray-500">Status: 
                    <span id="calendarStatus" class="@if($business->automation_settings['auto_calendar'] ?? false) text-green-500 @else text-gray-500 @endif">
                        {{ ($business->automation_settings['auto_calendar'] ?? false) ? 'Ativo' : 'Inativo' }}
                    </span>
                </p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" 
                       id="calendarToggle" 
                       class="sr-only peer"
                       onchange="toggleAutomation('calendar', this.checked)"
                       @if($business->automation_settings['auto_calendar'] ?? false) checked @endif
                       @if(!$business->is_connected) disabled @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></div>
            </label>
        </div>
    </div>
</div>

<!-- Grid Principal -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Calendário -->
    <div class="lg:col-span-3">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Calendário de Publicações</h3>
                <button onclick="refreshCalendar()" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Atualizar
                </button>
            </div>
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Painéis Laterais -->
    <div class="space-y-6">
        <!-- Próximas Publicações -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Próximas Publicações</h3>
            <div id="upcoming-posts" class="space-y-4">
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="space-y-3 mt-4">
                        <div class="h-4 bg-gray-200 rounded"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sugestões de IA -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Sugestões da IA</h3>
            <div id="ai-suggestions" class="space-y-4">
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="space-y-3 mt-4">
                        <div class="h-4 bg-gray-200 rounded"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Evento -->
<div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" aria-modal="true">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Detalhes do Evento</h3>
            <form id="eventForm" class="space-y-4">
                <input type="hidden" id="eventId">
                <div>
                    <label for="eventTitle" class="block text-sm font-medium text-gray-700">Título</label>
                    <input type="text" id="eventTitle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="eventStart" class="block text-sm font-medium text-gray-700">Início</label>
                    <input type="datetime-local" id="eventStart" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="eventEnd" class="block text-sm font-medium text-gray-700">Fim</label>
                    <input type="datetime-local" id="eventEnd" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEventModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    loadUpcomingPosts();
    loadAISuggestions();
    
    // Event listener para o formulário de evento
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const eventId = this.dataset.eventId;
        
        fetch(`/api/automation/calendar-events${eventId ? `/${eventId}` : ''}`, {
            method: eventId ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEventModal();
                calendar.refetchEvents();
                Swal.fire({
                    icon: 'success',
                    title: `Evento ${eventId ? 'atualizado' : 'criado'} com sucesso!`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    });
});

// Inicialização do Calendário
function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['dayGrid', 'timeGrid'],
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        defaultView: 'dayGridMonth',
        events: `/api/automation/calendar-events/${businessId}`,
        eventClick: function(info) {
            showEventModal(info.event);
        },
        dateClick: function(info) {
            showEventModal(null, info.date);
        },
        editable: true,
        eventDrop: function(info) {
            updateEvent(info.event);
        },
        eventResize: function(info) {
            updateEvent(info.event);
        }
    });
    calendar.render();
    window.calendar = calendar;
}

// Toggle de Automação
function toggleAutomation(type, enabled) {
    fetch(`/api/business/{{ $business->id }}/automation/${type}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ enabled: enabled })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const statusElement = document.getElementById(`${type}Status`);
            statusElement.textContent = enabled ? 'Ativo' : 'Inativo';
            statusElement.className = enabled ? 'text-green-500' : 'text-gray-500';
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: `Automação ${enabled ? 'ativada' : 'desativada'} com sucesso.`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Ocorreu um erro ao alterar a automação.',
        });
    });
}

// Funções do Modal de Evento
function showEventModal(event = null, date = null) {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    const titleInput = document.getElementById('eventTitle');
    const startInput = document.getElementById('eventStart');
    const endInput = document.getElementById('eventEnd');
    
    if (event) {
        titleInput.value = event.title;
        startInput.value = event.start.toISOString().slice(0, 16);
        endInput.value = event.end ? event.end.toISOString().slice(0, 16) : '';
        form.dataset.eventId = event.id;
    } else {
        form.reset();
        delete form.dataset.eventId;
        if (date) {
            startInput.value = date.toISOString().slice(0, 16);
        }
    }
    
    modal.classList.remove('hidden');
}

function updateEvent(event) {
    fetch(`/api/automation/calendar-events/${event.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            title: event.title,
            start: event.start,
            end: event.end
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Evento atualizado com sucesso!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar evento:', error);
        calendar.refetchEvents();
    });
}

function closeEventModal() {
    document.getElementById('eventModal').classList.add('hidden');
}

// Atualização do Calendário
function refreshCalendar() {
    window.calendar.refetchEvents();
    loadUpcomingPosts();
    loadAISuggestions();
}

// Carregamento de Posts Futuros
function loadUpcomingPosts() {
    fetch(`/api/automation/upcoming-posts/${businessId}`)  // Adicionar businessId
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('upcoming-posts');
            if (data.posts && data.posts.length > 0) {
                container.innerHTML = data.posts.map(post => `
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="font-medium">${post.title}</p>
                        <p class="text-sm text-gray-500">${new Date(post.scheduled_for).toLocaleDateString()}</p>
                        <p class="text-xs text-gray-400">${post.status}</p>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-gray-500">Nenhuma publicação agendada</p>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar posts:', error);
        });
}

function loadAISuggestions() {
    fetch(`/api/automation/ai-suggestions/${businessId}`)  // Adicionar businessId
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('ai-suggestions');
            if (data.insights && Object.keys(data.insights).length > 0) {
                container.innerHTML = `
                    <div class="space-y-4">
                        ${Object.entries(data.insights).map(([type, content]) => `
                            <div class="border-l-4 border-green-500 pl-4">
                                <h4 class="font-medium mb-2">${type.charAt(0).toUpperCase() + type.slice(1)}</h4>
                                <p class="text-sm text-gray-600">${content}</p>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else {
                container.innerHTML = '<p class="text-gray-500">Nenhuma sugestão disponível no momento</p>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar sugestões:', error);
        });
}
</script>
@endpush

</div>
</x-app-layout>