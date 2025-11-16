@php /** @var \Illuminate\Support\Collection|\App\Models\Project[] $projects */ @endphp

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-2">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-2">
        <ul class="list-disc ms-6">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <!-- Botão para abrir modal -->
            <button 
                x-data 
                @click="$dispatch('open-create-project')" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                + Criar projeto
            </button>
        </div>
    </x-slot>

    <div x-data="{ openCreate:false }"
         x-on:open-create-project.window="openCreate = true"
         class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- 1) Estado atual: SEM projetos -->
            <section class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900">Seus projetos</h3>

                @php
                    /** Ex.: na vida real virá do controller: $projects = Project::where('user_id', auth()->id())->get(); */
                    $projects = $projects ?? collect(); // vazio por enquanto
                @endphp

                @if($projects->isEmpty())
                    <div class="border rounded-xl p-10 text-center mt-4 bg-gray-50">
                        <div class="text-3xl mb-2">🗂️</div>
                        <h4 class="text-base font-medium text-gray-700">Você ainda não tem projetos</h4>
                        <p class="text-sm text-gray-500">Crie seu primeiro projeto para começar a organizar o trabalho.</p>
                        <div class="mt-4">
                            <button 
                                @click="openCreate = true"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                Criar projeto
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Quando houver dados de verdade, renderize-os aqui -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-4">
                        @foreach($projects as $p)
                            <article class="border rounded-xl p-5 bg-white shadow-sm">
                                <div class="flex items-start justify-between">
                                    <h4 class="font-semibold text-gray-900">{{ $p->nome }}</h4>
                                    <span class="text-xs px-2 py-1 rounded-full 
                                        {{ $p->status === 'Ativo' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $p->status }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-2 line-clamp-3">{{ $p->descricao }}</p>
                                <dl class="mt-4 text-xs text-gray-500 space-y-1">
                                    <div class="flex justify-between">
                                        <dt>Início</dt><dd>{{ \Carbon\Carbon::parse($p->inicio)->format('d/m/Y') }}</dd>
                                    </div>
                                    @if(!empty($p->fim))
                                    <div class="flex justify-between">
                                        <dt>Fim</dt><dd>{{ \Carbon\Carbon::parse($p->fim)->format('d/m/Y') }}</dd>
                                    </div>
                                    @endif
                                </dl>
                                <div class="mt-4 flex items-center justify-end gap-2">

                                    <!-- Botão Abrir -->
                                    <a href="{{ route('projects.show', $p) }}"
                                    class="px-3 py-1 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700 transition">
                                        Abrir
                                    </a>

                                    @if(auth()->id() === $p->owner_id)
                                        <!-- Link Editar -->
                                        <a href="{{ route('projects.edit', $p) }}" 
                                        class="text-gray-600 hover:underline text-sm">
                                            Editar
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <!-- 3) MODAL: Criar Projeto -->
        <div 
            x-cloak
            x-show="openCreate"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="openCreate=false"></div>

            <!-- content -->
            <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-start justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Criar projeto</h3>
                    <button class="text-gray-500 hover:text-gray-700" @click="openCreate=false">✖</button>
                </div>

                <form class="mt-4 space-y-4" method="POST" action="{{ route('projects.store', [], false) }}">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome do projeto</label>
                        <input required name="nome" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ex.: Painel de Demandas Orçamentárias">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea name="descricao" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Breve resumo do objetivo e escopo"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="Ativo">Ativo</option>
                                <option value="Pausado">Pausado</option>
                                <option value="Concluído">Concluído</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Início</label>
                            <input required name="inicio" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Fim (opcional)</label>
                            <input name="fim" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="openCreate=false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
