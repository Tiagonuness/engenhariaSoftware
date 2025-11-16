{{-- resources/views/partials/taskCard.blade.php --}}

@php
    $priorityColor = match($task->priority){
        'Baixa' => 'border-l-blue-400',
        'MEDIUM' => 'border-l-yellow-400',
        'HIGH' => 'border-l-red-400',
        default => 'border-l-gray-400',
    };
@endphp

<div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-3 border-l-4 {{ $priorityColor }} space-y-2">

    {{-- TITLE + ATRASADO --}}
    <a href="{{ route('tasks.show', $task) }}" class="block">
        <div class="flex justify-between items-start">
            <h5 class="font-medium text-sm text-gray-900 line-clamp-2">
                {{ $task->title }}
            </h5>

            @if($task->due_date && $task->due_date->isPast() && $task->status !== 'DONE')
                <span class="text-xs text-red-500 font-semibold ml-2">ATRASADO</span>
            @endif
        </div>
    </a>

    {{-- PRIORITY + ASSIGNEE + DUE DATE --}}
    <div class="flex justify-between items-center text-xs text-gray-500">
        <span class="capitalize px-2 py-0.5 rounded-full bg-gray-100">
            {{ strtolower($task->priority) }}
        </span>

        <div class="flex items-center space-x-2">
            @if($task->assignee)
                <span title="Assignee: {{ $task->assignee->name }}">
                    <span class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-800 flex items-center justify-center text-xs font-semibold">
                        {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                    </span>
                </span>
            @endif

            @if($task->due_date)
                <span class="flex items-center" title="Due date: {{ $task->due_date->format('d/m/Y') }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $task->due_date->format('d/m') }}
                </span>
            @endif
        </div>
    </div>

    {{-- STATUS ACTIONS (mini-buttons) --}}
    <div class="pt-2 border-t flex gap-2 flex-wrap">

        {{-- ========== STATUS = OPEN (A fazer) ========== --}}
        @if($task->status === 'OPEN')
            <form method="POST" action="{{ route('tasks.updateStatus', $task) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="IN_PROGRESS">
                <button type="submit"
                        class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200">
                    ▶️ Começar
                </button>
            </form>
        @endif

        {{-- ========== STATUS = IN_PROGRESS ========== --}}
        @if($task->status === 'IN_PROGRESS')
            <form method="POST" action="{{ route('tasks.updateStatus', $task) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="TEST">
                <button type="submit"
                        class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-700 hover:bg-yellow-200">
                    ⏭ Mover para Teste
                </button>
            </form>
        @endif

        {{-- ========== STATUS = TEST ========== --}}
        @if($task->status === 'TEST')
            <form method="POST" action="{{ route('tasks.updateStatus', $task) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="DONE">
                <button type="submit"
                        class="text-xs px-2 py-1 rounded bg-green-100 text-green-700 hover:bg-green-200">
                    ✔️ Marcar como Concluído
                </button>
            </form>
        @endif

        {{-- ========== STATUS = DONE ========== --}}
        @if($task->status === 'DONE')
            <form method="POST" action="{{ route('tasks.updateStatus', $task) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="OPEN">
                <button type="submit"
                        class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 hover:bg-gray-200">
                    🔁 Reabrir (A fazer)
                </button>
            </form>
        @endif

    </div>
</div>
