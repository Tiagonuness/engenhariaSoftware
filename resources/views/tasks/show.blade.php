<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tarefa: {{ $task->title }}
            </h2>
            <a href="{{ route('projects.show', $project) }}"
               class="px-3 py-2 rounded-lg bg-gray-100 text-gray-800 text-sm">
                ← Voltar para o projeto
            </a>
        </div>
    </x-slot>

    <div class="py-10 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
            <div class="rounded-lg bg-green-50 text-green-800 px-4 py-2">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6 space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $task->title }}
                </h3>
                @if($task->description)
                    <p class="mt-2 text-gray-700">{{ $task->description }}</p>
                @endif
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">Projeto</dt>
                    <dd>{{ $project->nome }}</dd>
                </div>

                <div>
                    <dt class="font-medium text-gray-500">Responsável</dt>
                    <dd>{{ $task->assignee?->name ?? 'Não atribuído' }}</dd>
                </div>

                <div>
                    <dt class="font-medium text-gray-500">Status atual</dt>
                    <dd>{{ $task->status }}</dd>
                </div>

                <div>
                    <dt class="font-medium text-gray-500">Prioridade</dt>
                    <dd>{{ $task->priority }}</dd>
                </div>

                <div>
                    <dt class="font-medium text-gray-500">Prazo</dt>
                    <dd>{{ $task->due_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
            </dl>

            {{-- Formulário para mudar o status --}}
            <div class="border-t pt-4">
                <form method="POST" action="{{ route('tasks.updateStatus', $task) }}"
                      class="flex flex-col md:flex-row gap-3 items-start md:items-center">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Mudar status para:
                        </label>
                        <select name="status" class="mt-1 rounded-lg border-gray-300 shadow-sm">
                            <option value="OPEN"        @selected($task->status === 'OPEN')>A Fazer</option>
                            <option value="IN_PROGRESS" @selected($task->status === 'IN_PROGRESS')>Em andamento</option>
                            <option value="TEST"     @selected($task->status === 'TEST')>Bloqueada</option>
                            <option value="DONE"        @selected($task->status === 'DONE')>Concluída</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="mt-2 md:mt-6 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                        Atualizar status
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
