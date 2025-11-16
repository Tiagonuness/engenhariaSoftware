<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar projeto
            </h2>

            <a href="{{ route('dashboard') }}"
            class="px-3 py-1.5 rounded-lg bg-gray-200 text-gray-700 text-sm hover:bg-gray-300 transition">
                ← Voltar ao painel
            </a>
        </div>
    </x-slot>

    <div class="py-10 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- FORM DE EDIÇÃO DO PROJETO --}}
        <form class="bg-white p-6 rounded-xl shadow space-y-4"
              method="POST" action="{{ route('projects.update', $project) }}">
            @csrf @method('PUT')

            @if($errors->any())
                <div class="rounded bg-red-50 text-red-800 px-4 py-2">
                    <ul class="list-disc ms-6">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="text-sm">Nome</label>
                <input name="nome" class="w-full mt-1 rounded border-gray-300"
                       value="{{ old('nome',$project->nome) }}">
            </div>

            <div>
                <label class="text-sm">Descrição</label>
                <textarea name="descricao" rows="4"
                          class="w-full mt-1 rounded border-gray-300">{{ old('descricao',$project->descricao) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm">Status</label>
                    <select name="status" class="w-full mt-1 rounded border-gray-300">
                        @foreach(['Ativo','Pausado','Concluído'] as $st)
                            <option value="{{ $st }}" {{ $project->status===$st ? 'selected':'' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm">Início</label>
                    <input type="date" name="inicio" class="w-full mt-1 rounded border-gray-300"
                           value="{{ old('inicio', $project->inicio->toDateString()) }}">
                </div>
                <div>
                    <label class="text-sm">Fim</label>
                    <input type="date" name="fim" class="w-full mt-1 rounded border-gray-300"
                           value="{{ old('fim', $project->fim?->toDateString()) }}">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 rounded border">Cancelar</a>
                <button class="px-4 py-2 rounded bg-blue-600 text-white">Salvar</button>
            </div>
        </form>

        {{-- GESTÃO DE MEMBROS DO PROJETO --}}
        <div class="bg-white p-6 rounded-xl shadow space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-lg text-gray-800">Membros do projeto</h3>
                @if(auth()->id() === $project->owner_id)
                    <span class="text-xs text-gray-500">Você é o dono do projeto</span>
                @endif
            </div>

            {{-- LISTA DE MEMBROS --}}
            @if($project->members->count())
                <ul class="divide-y divide-gray-100">
                    @foreach($project->members as $member)
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                    {{ $member->name }}
                                    @if($member->id === $project->owner_id)
                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">
                                            Dono
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $member->email }}
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs
                                             bg-blue-50 text-blue-700">
                                    {{ $member->pivot->level ?? '—' }}
                                </span>

                                @if(auth()->id() === $project->owner_id && $member->id !== $project->owner_id)
                                    <form method="POST"
                                          action="{{ route('projects.members.destroy', [$project, $member]) }}"
                                          onsubmit="return confirm('Remover este membro do projeto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-red-600 hover:underline">
                                            Remover
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">Nenhum membro adicionado ainda.</p>
            @endif

            {{-- FORM PARA ADICIONAR MEMBRO --}}
            @if(auth()->id() === $project->owner_id)
                <div class="pt-4 border-t border-gray-100">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Adicionar membro</h4>

                    <form method="POST" action="{{ route('projects.members.store', $project) }}"
                        class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        @csrf

                        <div>
                            <label class="text-xs text-gray-600">Usuário (e-mail)</label>
                            <input type="text" name="email" class="w-full mt-1 rounded border-gray-300 text-sm"
                                placeholder="usuario@exemplo.com" value="{{ old('email') }}">
                        </div>

                        <div>
                            <label class="text-xs text-gray-600">Nível</label>
                            <select name="role" class="w-full mt-1 rounded border-gray-300 text-sm">
                                <option value="PRODUCT_OWNER">Product Owner</option>
                                <option value="SCRUM_MASTER">Scrum Master</option>
                                <option value="DEVELOPER">Dev</option>
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <button class="px-4 py-2 rounded bg-green-600 text-white text-sm">
                                Adicionar membro
                            </button>
                        </div>
                    </form>

                    {{-- ERROS --}}
                    @if(session('error'))
                        <p class="mt-3 text-sm text-red-600">{{ session('error') }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
