<x-app-layout>
    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.css' rel='stylesheet' />
    @endpush

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Painel de Controle Principal -->
            <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-google-sans mb-4">Painel de Controle de Automação</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Controle de Posts Automáticos -->
                    <div class="automation-control-card">
                        <h4 class="font-semibold mb-2">Posts Automáticos</h4>
                        <label class="switch">
                            <input type="checkbox" id="autoPostToggle" 
                                   onchange="toggleAutomation('posts')"
                                   @if($business->automation_settings['auto_posts'] ?? false) checked @endif>
                            <span class="slider round"></span>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Permite que a IA crie e agende posts automaticamente</p>
                    </div>

                    <!-- Controle de Análise de Tendências -->
                    <div class="automation-control-card">
                        <h4 class="font-semibold mb-2">Análise de Tendências</h4>
                        <label class="switch">
                            <input type="checkbox" id="trendAnalysisToggle" 
                                   onchange="toggleAutomation('trends')"
                                   @if($business->automation_settings['trend_analysis'] ?? false) checked @endif>
                            <span class="slider round"></span>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Monitora tendências do seu segmento</p>
                    </div>

                    <!-- Controle de Proteção Automática -->
                    <div class="automation-control-card">
                        <h4 class="font-semibold mb-2">Proteção Automática</h4>
                        <label class="switch">
                            <input type="checkbox" id="autoProtectionToggle" 
                                   onchange="toggleAutomation('protection')"
                                   @if($business->automation_settings['auto_protection'] ?? false) checked @endif>
                            <span class="slider round"></span>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Monitora e protege sua presença online</p>
                    </div>
                </div>
            </div>

            <!-- Grid Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Coluna Esquerda -->
                <div class="space-y-6">
                    <!-- Calendário -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-google-sans">Calendário de Publicações</h3>
                                <button onclick="refreshCalendarEvents()" 
                                        class="btn-secondary">
                                    Atualizar Calendário
                                </button>
                            </div>
                            <div id="calendar-container"></div>
                        </div>
                    </div>

                    <!-- Análise Competitiva -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-google-sans">Análise Competitiva</h3>
                                <button onclick="getCompetitiveAnalysis()" 
                                        class="btn-primary">
                                    Atualizar Análise
                                </button>
                            </div>
                            <div id="competitive-analysis-container" class="space-y-4">
                                <!-- Conteúdo será carregado via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita -->
                <div class="space-y-6">
                    <!-- Sugestões de IA -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-google-sans">Sugestões da IA</h3>
                                <button onclick="refreshAISuggestions()" 
                                        class="btn-secondary">
                                    Atualizar Sugestões
                                </button>
                            </div>
                            <div id="ai-suggestions-container" class="space-y-4">
                                <!-- Sugestões serão carregadas via AJAX -->
                            </div>
                        </div>
                    </div>

                    <!-- Eventos Sazonais -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-google-sans">Eventos Sazonais</h3>
                                <button onclick="checkSeasonalEvents()" 
                                        class="btn-primary">
                                    Verificar Eventos
                                </button>
                            </div>
                            <div id="seasonal-events-container" class="space-y-4">
                                <!-- Eventos sazonais serão carregados via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eventos -->
    @include('automation.modals.event-modal')

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.umd.min.js'></script>

    <script>
        let calendar;

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            loadInitialData();
            setupEventListeners();
        });

        // Funções principais
        function initializeCalendar() {
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
                select: openEventModal,
                eventClick: handleEventClick,
                eventDrop: handleEventDrop,
                eventDidMount: function(info) {
                    tippy(info.el, {
                        content: `${info.event.title}<br>${info.event.extendedProps.description || ''}`,
                        allowHTML: true
                    });
                }
            });
            calendar.render();
        }

        function loadInitialData() {
            refreshAISuggestions();
            getCompetitiveAnalysis();
            checkSeasonalEvents();
        }

        // Funções de automação
        async function toggleAutomation(type) {
            try {
                const response = await fetch(`/automation/toggle/${type}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                showNotification(data.message, data.success ? 'success' : 'error');
            } catch (error) {
                showNotification('Erro ao alterar automação', 'error');
            }
        }

        // Funções do calendário
        async function refreshCalendarEvents() {
            try {
                const response = await fetch('/automation/calendar-events');
                const events = await response.json();
                calendar.removeAllEvents();
                calendar.addEventSource(events);
            } catch (error) {
                showNotification('Erro ao atualizar calendário', 'error');
            }
        }

        function openEventModal(selectInfo) {
            const modal = document.getElementById('event-modal');
            const form = document.getElementById('event-form');
            
            // Reset form
            form.reset();
            document.getElementById('event-id').value = '';
            
            // Set initial dates
            document.getElementById('event-start').value = selectInfo.startStr;
            document.getElementById('event-end').value = selectInfo.endStr;
            
            modal.classList.remove('hidden');
        }

        async function handleEventClick(info) {
            const modal = document.getElementById('event-modal');
            const form = document.getElementById('event-form');
            
            // Fill form with event data
            document.getElementById('event-id').value = info.event.id;
            document.getElementById('event-title').value = info.event.title;
            document.getElementById('event-description').value = info.event.extendedProps.description || '';
            document.getElementById('event-start').value = info.event.startStr;
            document.getElementById('event-end').value = info.event.endStr;
            document.getElementById('event-type').value = info.event.extendedProps.type || 'custom';
            
            modal.classList.remove('hidden');
        }

        async function handleEventDrop(info) {
            try {
                const response = await fetch(`/automation/calendar-events/${info.event.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        start: info.event.startStr,
                        end: info.event.endStr
                    })
                });
                
                if (!response.ok) {
                    info.revert();
                    throw new Error('Erro ao atualizar evento');
                }
                
                showNotification('Evento atualizado com sucesso', 'success');
            } catch (error) {
                info.revert();
                showNotification(error.message, 'error');
            }
        }

        // Funções de IA e análise
        async function refreshAISuggestions() {
            const container = document.getElementById('ai-suggestions-container');
            container.innerHTML = '<div class="loading-skeleton"></div>';
            
            try {
                const response = await fetch('/automation/ai-suggestions');
                const data = await response.json();
                
                container.innerHTML = data.suggestions.map(suggestion => `
                    <div class="suggestion-card p-4 border border-gray-200 rounded-lg">
                        <h4 class="font-semibold">${suggestion.title}</h4>
                        <p class="text-gray-600 mt-2">${suggestion.message}</p>
                        <div class="mt-4 flex space-x-2">
                            <button onclick="handleAISuggestion('${suggestion.type}', '${suggestion.action}', ${JSON.stringify(suggestion.data || {})})" 
                                    class="btn-primary">
                                Aplicar
                            </button>
                            <button onclick="dismissSuggestion('${suggestion.id}')" 
                                    class="btn-secondary">
                                Ignorar
                            </button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                showNotification('Erro ao carregar sugestões', 'error');
            }
        }

        async function getCompetitiveAnalysis() {
            const container = document.getElementById('competitive-analysis-container');
            container.innerHTML = '<div class="loading-skeleton"></div>';
            
            try {
                const response = await fetch('/automation/competitive-analysis');
                const data = await response.json();
                
                container.innerHTML = `
                    <div class="space-y-4">
                        ${data.competitors.map(competitor => `
                            <div class="competitor-card p-4 border border-gray-200 rounded-lg">
                                <h4 class="font-semibold">${competitor.name}</h4>
                                <p class="text-gray-600 mt-2">${competitor.analysis}</p>
                                <div class="mt-2 flex space-x-2">
                                    ${competitor.suggestions.map(suggestion => `
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            ${suggestion}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } catch (error) {
                showNotification('Erro ao carregar análise competitiva', 'error');
            }
        }

        async function checkSeasonalEvents() {
            const container = document.getElementById('seasonal-events-container');
            container.innerHTML = '<div class="loading-skeleton"></div>';
            
            try {
                const response = await fetch('/automation/segment-events/' + {{ $business->id }});
                const data = await response.json();
                
                container.innerHTML = data.suggestions.map(event => `
                    <div class="event-card p-4 border border-gray-200 rounded-lg">
                        <h4 class="font-semibold">${event.title}</h4>
                        <p class="text-gray-600 mt-2">${event.message}</p>
                        <div class="mt-4 flex space-x-2">
                            <button onclick="addToCalendar(${JSON.stringify(event)})" 
                                    class="btn-primary">
                                Adicionar ao Calendário
                            </button>
                            <button onclick="createAutomatedPost(${JSON.stringify(event)})" 
                                    class="btn-secondary">
                                Criar Post
                            </button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                showNotification('Erro ao verificar eventos sazonais', 'error');
            }
        }

        // Funções auxiliares
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            } animate-fade-in z-50`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('animate-fade-out');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Event Listeners
        function setupEventListeners() {
            // Modal close buttons
            document.querySelectorAll('.modal-close').forEach(button => {
                button.addEventListener('click', () => {
                    button.closest('.modal').classList.add('hidden');
                });
            });

            // Event form submission
            document.getElementById('event-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                
                try {
                    const response = await fetch('/automation/create-event', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    });
                    
                    if (!response.ok) throw new Error('Erro ao criar evento');
                    
                    const data = await response.json();
                    calendar.addEvent(data);
                    e.target.closest('.modal').classList.add('hidden');
                    showNotification('Evento criado com sucesso', 'success');
                } catch (error) {
                    showNotification(error.message, 'error');
                }
            });
        }
    </script>
    @endpush
</x-app-layout>