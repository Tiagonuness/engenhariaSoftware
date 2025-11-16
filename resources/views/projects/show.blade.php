<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projeto: {{ $project->nome }}
            </h2>
            @if(auth()->id() === $project->owner_id)
                <a class="px-3 py-2 rounded-lg bg-blue-600 text-white" href="{{ route('projects.edit', $project) }}">Editar</a>
            @endif
        </div>
    </x-slot>

    <div x-data="{ openTaskModal: false }" class="py-10 max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-8">

        {{-- flashes --}}
        @if(session('success'))
            <div class="rounded-lg bg-green-50 text-green-800 px-4 py-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 text-red-800 px-4 py-2">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-red-50 text-red-800 px-4 py-2">
                <ul class="list-disc ms-6">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            {{-- Linha 1: Cards de Progresso --}}
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Progresso</div>
                <div class="mt-2 flex items-baseline gap-2">
                    <div class="text-2xl font-semibold">{{ $progress }}%</div>
                    <div class="text-sm text-gray-500">({{ $done }} de {{ $total }})</div>
                </div>
                <div class="mt-3 h-2 bg-gray-200 rounded-full">
                    <div class="h-2 bg-green-500 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">A fazer</div>
                <div class="text-2xl font-semibold mt-2">{{ $open }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Em andamento</div>
                <div class="text-2xl font-semibold mt-2">{{ $inprogress }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Em teste</div>
                <div class="text-2xl font-semibold mt-2">{{ $test }}</div>
            </div>
            
            {{-- Linha 2 --}}
            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Atrasadas</div>
                <div class="text-2xl font-semibold mt-2">{{ $overdue }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Próxima entrega</div>
                <div class="mt-2 text-lg font-medium">
                    {{ $nextDue?->due_date?->format('d/m/Y') ?? '—' }}
                </div>
                @if($nextDue)
                    <div class="text-sm text-gray-500 line-clamp-2">{{ $nextDue->title }}</div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Membros</div>
                <div class="text-2xl font-semibold mt-2">{{ $project->members->count() }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-sm text-gray-500">Seu papel</div>
                <div class="text-lg font-medium mt-2">{{ $userRole }}</div>
            </div>
        </section>

        {{-- AÇÕES RÁPIDAS --}}
        @if(in_array($userRole, ['OWNER', 'PRODUCT_OWNER', 'SCRUM_MASTER']))
        <section class="grid grid-cols-1">
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-lg">Ações rápidas</h3>

                <div class="mt-4 flex flex-col md:flex-row gap-3">
                    <button type="button"
                            @click="openTaskModal = true"
                            class="flex-1 px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition text-center">
                        + Nova tarefa
                    </button>

                    @if(auth()->id() === $project->owner_id)
                        <a href="{{ route('projects.edit', $project) }}"
                           class="flex-1 text-center px-4 py-2 rounded-lg border hover:bg-gray-50 transition">
                            Configurar projeto
                        </a>
                    @endif
                </div>
            </div>
        </section>
        @endif

        {{-- MODAL: Nova tarefa --}}
        <div x-cloak
             x-show="openTaskModal"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">

            <div @click.away="openTaskModal = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4">

                {{-- Cabeçalho do modal --}}
                <div class="flex items-center justify-between px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Nova tarefa
                    </h3>
                    <button type="button"
                            @click="openTaskModal = false"
                            class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>

                {{-- Formulário --}}
                <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="px-6 py-5 space-y-4">
                    @csrf

                    <div class="grid md:grid-cols-2 gap-4">
                        {{-- Título --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Título da tarefa
                            </label>
                            <input type="text" name="title"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
                                   placeholder="Ex: Implementar tela de login" required>
                        </div>

                        {{-- Responsável --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Responsável
                            </label>
                            <select name="assignee_id"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">— Não atribuir —</option>
                                @foreach($project->members as $member)
                                    <option value="{{ $member->id }}">
                                        {{ $member->name }} ({{ $member->pivot->role }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Prazo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Prazo
                            </label>
                            <input type="date" name="due_date"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm">
                        </div>

                        {{-- Prioridade --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Prioridade
                            </label>
                            <select name="priority"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="BAIXA">Baixa</option>
                                <option value="MÉDIA" selected>Média</option>
                                <option value="ALTA">Alta</option>
                            </select>
                        </div>

                        {{-- Status inicial --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Status inicial
                            </label>
                            <select name="status"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm">
                                {{-- Mantém os values (OPEN, etc.), mas troca o texto --}}
                                <option value="OPEN" selected>A fazer</option>
                                <option value="IN_PROGRESS">Em andamento</option>
                                <option value="TEST">Teste</option>
                                <option value="DONE">Concluída</option>
                            </select>
                        </div>
                    </div>

                    {{-- Descrição opcional --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Descrição (opcional)
                        </label>
                        <textarea name="description" rows="3"
                                  class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
                                  placeholder="Detalhes da tarefa, critérios de aceite etc."></textarea>
                    </div>

                    {{-- Rodapé do modal --}}
                    <div class="flex justify-end gap-3 pt-2 border-t mt-2">
                        <button type="button"
                                @click="openTaskModal = false"
                                class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">
                            Criar tarefa
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- QUADRO DE TAREFAS --}}
        <section>
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-xl">Quadro de Tarefas</h3>
            </div>

            {{-- Contêiner principal do Kanban: sem scroll horizontal, colunas ocupam a largura --}}
            <div class="flex gap-6">

                {{-- COLUNA: A FAZER (OPEN) --}}
                <div class="flex-1 min-w-0 bg-gray-50 rounded-xl shadow p-4">
                    <h4 class="font-bold text-gray-800 mb-4">
                        A fazer ({{ $tasksByStatus['OPEN']->count() ?? 0 }})
                    </h4>
                    <div class="space-y-3 h-full max-h-[70vh] overflow-y-auto pr-1">
                        @forelse($tasksByStatus['OPEN'] ?? [] as $t)
                            @include('partials.taskCard', ['task' => $t])
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Nenhuma tarefa a fazer.</p>
                        @endforelse
                    </div>
                </div>

                {{-- COLUNA: EM ANDAMENTO --}}
                <div class="flex-1 min-w-0 bg-gray-50 rounded-xl shadow p-4">
                    <h4 class="font-bold text-gray-800 mb-4">
                        Em andamento ({{ $tasksByStatus['IN_PROGRESS']->count() ?? 0 }})
                    </h4>
                    <div class="space-y-3 h-full max-h-[70vh] overflow-y-auto pr-1">
                        @forelse($tasksByStatus['IN_PROGRESS'] ?? [] as $t)
                            @include('partials.taskCard', ['task' => $t])
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Nenhuma tarefa em andamento.</p>
                        @endforelse
                    </div>
                </div>
                <div class="flex-1 min-w-0 bg-gray-50 rounded-xl shadow p-4">
                    <h4 class="font-bold text-gray-800 mb-4">
                        Teste ({{ $tasksByStatus['TEST']->count() ?? 0 }})
                    </h4>
                    <div class="space-y-3 h-full max-h-[70vh] overflow-y-auto pr-1">
                        @forelse($tasksByStatus['TEST'] ?? [] as $t)
                            @include('partials.taskCard', ['task' => $t])
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Nenhuma tarefa em teste.</p>
                        @endforelse
                    </div>
                </div>

                {{-- COLUNA: CONCLUÍDO --}}
                <div class="flex-1 min-w-0 bg-gray-50 rounded-xl shadow p-4">
                    <h4 class="font-bold text-gray-800 mb-4">
                        Concluído ({{ $tasksByStatus['DONE']->count() ?? 0 }})
                    </h4>
                    <div class="space-y-3 h-full max-h-[70vh] overflow-y-auto pr-1">
                        @forelse($tasksByStatus['DONE'] ?? [] as $t)
                            @include('partials.taskCard', ['task' => $t])
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Nenhuma tarefa concluída.</p>
                        @endforelse
                    </div>
                </div>

            </div>
            
        </section>

    </div>
</x-app-layout>