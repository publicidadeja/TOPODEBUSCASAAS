<x-app-layout>
    @push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css' rel='stylesheet' />
    <style>
        .automation-card {
            transition: all 0.3s ease;
        }
        .automation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .calendar-container {
            height: 600px;
            overflow: hidden;
        }
        .suggestion-card {
            border-left: 4px solid #4F46E5;
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
    @endpush

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Cabeçalho -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    Automação do Google Meu Negócio
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Gerencie suas automações e mantenha seu negócio atualizado automaticamente
                </p>
            </div>


                        <!-- Painel de Status -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Status da Integração -->
                <div class="automation-card bg-white p-6 rounded-lg shadow-sm">
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
                                    <a href="{{ route('business.settings', $business) }}" class="text-blue-600 hover:text-blue-800">
                                        Configurar integração
                                    </a>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Automação de Posts -->
                <div class="automation-card bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Posts Automáticos</h3>
                            <p class="text-sm text-gray-500">
                                Status: <span id="autoPostStatus" class="font-medium"></span>
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="autoPostToggle" class="sr-only peer" 
                                   @if($business->automation_settings['auto_posts'] ?? false) checked @endif>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full 
                                      peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 
                                      after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 
                                      after:w-5 after:transition-all"></div>
                        </label>
                    </div>
                </div>

                <!-- Calendário Inteligente -->
                <div class="automation-card bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Calendário Inteligente</h3>
                            <p class="text-sm text-gray-500">
                                Status: <span id="calendarStatus" class="font-medium"></span>
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="calendarToggle" class="sr-only peer"
                                   @if($business->automation_settings['smart_calendar'] ?? false) checked @endif>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full 
                                      peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 
                                      after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 
                                      after:w-5 after:transition-all"></div>
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
                <div class="flex space-x-2">
                    <button onclick="createNewPost()" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Novo Post
                    </button>
                    <button onclick="refreshCalendar()" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Atualizar
                    </button>
                </div>
            </div>
            <div id="calendar" class="calendar-container"></div>
        </div>
    </div>

    <!-- Painéis Laterais -->
    <div class="space-y-6">
        <!-- Próximas Publicações -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Próximas Publicações</h3>
            <div id="upcoming-posts" class="space-y-4">
                <!-- Loading State -->
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="space-y-3 mt-4">
                        <div class="h-4 bg-gray-200 rounded"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sugestões da IA -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Sugestões da IA</h3>
                <button onclick="refreshSuggestions()" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
            <div id="ai-suggestions" class="space-y-4">
                <!-- Loading State -->
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="space-y-3 mt-4">
                        <div class="h-4 bg-gray-200 rounded"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métricas Rápidas -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Métricas</h3>
            <div id="quick-metrics" class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Posts este mês</span>
                    <span class="font-medium" id="posts-count">--</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Engajamento médio</span>
                    <span class="font-medium" id="avg-engagement">--</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Melhor horário</span>
                    <span class="font-medium" id="best-time">--</span>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

<script>
    // Inicialização do Calendário
    document.addEventListener('DOMContentLoaded', function() {
        initializeCalendar();
        loadUpcomingPosts();
        loadAISuggestions();
        updateMetrics();
        
        // Inicializar listeners dos toggles
        document.getElementById('autoPostToggle').addEventListener('change', function(e) {
            toggleAutomation('posts', e.target.checked);
        });
        
        document.getElementById('calendarToggle').addEventListener('change', function(e) {
            toggleAutomation('calendar', e.target.checked);
        });
    });

    function initializeCalendar() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['dayGrid', 'timeGrid'],
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            locale: 'pt-br',
            defaultView: 'dayGridMonth',
            editable: true,
            eventLimit: true,
            events: '/automation/events',
            selectable: true,
            select: function(info) {
                showEventModal(null, info);
            },
            eventClick: function(info) {
                showEventModal(info.event);
            },
            eventDrop: function(info) {
                updateEvent(info.event);
            }
        });
        calendar.render();
        window.calendar = calendar;
    }

    function toggleAutomation(type, enabled) {
    // Mostrar loading
    const statusElement = document.getElementById(`${type}Status`);
    if (statusElement) {
        statusElement.textContent = 'Atualizando...';
    }

    fetch('/automation/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: type,
            enabled: enabled
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Atualiza o status na interface
            if (statusElement) {
                statusElement.textContent = enabled ? 'Ativo' : 'Inativo';
            }
            
            // Mostra notificação de sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.message || 'Erro ao alterar automação');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        
        // Reverte o toggle em caso de erro
        const toggleElement = document.getElementById(`${type}Toggle`);
        if (toggleElement) {
            toggleElement.checked = !enabled;
        }
        
        // Atualiza o status
        if (statusElement) {
            statusElement.textContent = enabled ? 'Inativo' : 'Ativo';
        }
        
        // Mostra erro
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Ocorreu um erro ao alterar a automação'
        });
    });
}

    function loadUpcomingPosts() {
        fetch('/automation/upcoming-posts')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('upcoming-posts');
                container.innerHTML = data.posts.map(post => `
                    <div class="p-4 border rounded-lg mb-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium">${post.title}</h4>
                                <p class="text-sm text-gray-500">${post.scheduled_for}</p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="editPost(${post.id})" class="text-blue-600 hover:text-blue-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="deletePost(${post.id})" class="text-red-600 hover:text-red-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            });
    }
    function loadAISuggestions() {
    const container = document.getElementById('ai-suggestions');
    container.innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="space-y-3 mt-4">
                <div class="h-4 bg-gray-200 rounded"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            </div>
        </div>
    `;

    fetch('/automation/ai-suggestions')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.suggestions.length > 0) {
                container.innerHTML = data.suggestions.map(suggestion => `
                    <div class="suggestion-card bg-white p-4 rounded-lg shadow-sm mb-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">${suggestion.title}</h4>
                                <p class="text-sm text-gray-500 mt-1">${suggestion.description}</p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="applySuggestion('${suggestion.id}')" 
                                        class="text-blue-600 hover:text-blue-800">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center py-4 text-gray-500">
                        <p>Nenhuma sugestão disponível no momento.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            container.innerHTML = `
                <div class="text-center py-4 text-red-500">
                    <p>Erro ao carregar sugestões. Tente novamente mais tarde.</p>
                </div>
            `;
        });
}

function getPriorityClass(priority) {
    switch (priority) {
        case 'high':
            return 'bg-red-100 text-red-800';
        case 'medium':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-green-100 text-green-800';
    }
}

// Função para aplicar sugestão
function applySuggestion(suggestionId) {
    fetch('/automation/apply-suggestion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ suggestion_id: suggestionId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Sugestão aplicada com sucesso',
                timer: 2000,
                showConfirmButton: false
            });
            loadAISuggestions(); // Recarrega as sugestões
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Erro ao aplicar sugestão'
        });
    });
}

    function updateMetrics() {
        fetch('/automation/metrics')
            .then(response => response.json())
            .then(data => {
                document.getElementById('posts-count').textContent = data.posts_count;
                document.getElementById('avg-engagement').textContent = data.avg_engagement;
                document.getElementById('best-time').textContent = data.best_time;
            });
    }

    function createNewPost() {
        Swal.fire({
            title: 'Novo Post',
            html: `
                <input id="post-title" class="swal2-input" placeholder="Título">
                <textarea id="post-content" class="swal2-textarea" placeholder="Conteúdo"></textarea>
                <input id="post-date" type="datetime-local" class="swal2-input">
            `,
            showCancelButton: true,
            confirmButtonText: 'Criar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                return {
                    title: document.getElementById('post-title').value,
                    content: document.getElementById('post-content').value,
                    scheduled_for: document.getElementById('post-date').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/automation/posts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(result.value)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Sucesso!', 'Post criado com sucesso', 'success');
                        window.calendar.refetchEvents();
                        loadUpcomingPosts();
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    Swal.fire('Erro', error.message, 'error');
                });
            }
        });
    }

    function editPost(postId) {
        fetch(`/automation/posts/${postId}`)
            .then(response => response.json())
            .then(post => {
                Swal.fire({
                    title: 'Editar Post',
                    html: `
                        <input id="post-title" class="swal2-input" value="${post.title}" placeholder="Título">
                        <textarea id="post-content" class="swal2-textarea" placeholder="Conteúdo">${post.content}</textarea>
                        <input id="post-date" type="datetime-local" class="swal2-input" value="${post.scheduled_for}">
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Salvar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        return {
                            title: document.getElementById('post-title').value,
                            content: document.getElementById('post-content').value,
                            scheduled_for: document.getElementById('post-date').value
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        updatePost(postId, result.value);
                    }
                });
            });
    }

    function updatePost(postId, data) {
        fetch(`/automation/posts/${postId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Sucesso!', 'Post atualizado com sucesso', 'success');
                window.calendar.refetchEvents();
                loadUpcomingPosts();
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            Swal.fire('Erro', error.message, 'error');
        });
    }

    function deletePost(postId) {
        Swal.fire({
            title: 'Confirmar exclusão',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/automation/posts/${postId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Excluído!', 'Post excluído com sucesso.', 'success');
                        window.calendar.refetchEvents();
                        loadUpcomingPosts();
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    Swal.fire('Erro', error.message, 'error');
                });
            }
        });
    }
</script>
@endpush
</x-app-layout>