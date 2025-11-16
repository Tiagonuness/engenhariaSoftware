<?php

namespace App\Http\Controllers;

use App\Enums\ProjectRole;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProjectController extends Controller
{
    /** Lista projetos do usuário logado (para o Dashboard) */
    public function index(Request $request)
    {
        $user = $request->user();

        // projetos em que ele é membro (inclui os que ele criou)
        $projects = $user->projects()
            ->with('owner')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard', compact('projects'));
    }

    public function store(StoreProjectRequest $request)
    {
        $user = $request->user();

        $project = Project::create([
            'owner_id'  => $user->id,
            'nome'      => $request->nome,
            'descricao' => $request->descricao,
            'status'    => $request->status,
            'inicio'    => $request->inicio,
            'fim'       => $request->fim,
        ]);

        $project->members()->attach($user->id, ['role' => ProjectRole::OWNER->value]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Projeto criado com sucesso!');
    }

    public function destroy(Project $project, Request $request)
    {
        abort_unless($project->owner_id === $request->user()->id, 403);
        $project->delete();

        return back()->with('success', 'Projeto removido.');
    }

   public function show(Project $project)
    {
        $this->authorizeView($project);

        $project->load([
            'owner',
            'members',
            'tasks' => function($q) {
                $q->orderBy('priority', 'desc')->orderBy('due_date', 'asc')->with('assignee');
            }
        ]);
        
        $total      = $project->tasks->count();
        $done       = $project->tasks->where('status','DONE')->count();
        $open       = $project->tasks->where('status','OPEN')->count();
        $inprogress = $project->tasks->where('status','IN_PROGRESS')->count();
        $test       = $project->tasks->where('status','TEST')->count();

        $tasks = $project->tasks;

        // atrasadas = qualquer tarefa com prazo no passado e não concluída
        $overdue = $tasks
            ->filter(fn($t) => $t->due_date && $t->due_date->isPast() && $t->status !== 'DONE')
            ->count();

        // próxima entrega = tarefas com prazo no futuro ou hoje (ainda não atrasadas) e não concluídas
        $nextDue = $tasks
            ->filter(fn($t) => $t->due_date && !$t->due_date->isPast() && $t->status !== 'DONE')
            ->sortBy('due_date')
            ->first();

        $progress = $total > 0 ? round(($done / $total) * 100) : 0;
        
        $tasksByStatus = $project->tasks->groupBy('status');

        $allStatuses = collect(['OPEN', 'IN_PROGRESS', 'TEST', 'DONE', 'OTHER_STATUS_IF_YOU_HAVE_IT']); // Adicione outros status se necessário
        
        $tasksByStatus = $allStatuses
            ->mapWithKeys(fn ($status) => [$status => $tasksByStatus->get($status) ?? collect()])
            ->all();

        $userRole = auth()->user()->id === $project->owner_id
            ? 'OWNER'
            : ($project->members->firstWhere('id', auth()->id())?->pivot?->role ?? '—');

        return view('projects.show', compact(
            'project',
            'total','done','open','inprogress','test','overdue','nextDue','progress',
            'userRole',
            'tasksByStatus' 
        ));
    }

    public function edit(Project $project)
    {
        $this->authorizeUpdate($project);
        $users = User::orderBy('name')->get();

        return view('projects.edit', compact('project', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeUpdate($project);

        $data = $request->validate([
            'nome'      => ['required','string','max:150'],
            'descricao' => ['nullable','string','max:5000'],
            'status'    => ['required', Rule::in(['Ativo','Pausado','Concluído'])],
            'inicio'    => ['required','date'],
            'fim'       => ['nullable','date','after_or_equal:inicio'],
        ]);

        $project->update($data);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projeto atualizado.');
    }

    public function membersStore(Request $request, Project $project)
    {
        $this->authorizeUpdate($project);

        // valida só formato do e-mail e role
        $data = $request->validate([
            'email' => ['required','email'],
            'role'  => ['required', Rule::in(['PRODUCT_OWNER','SCRUM_MASTER','DEVELOPER'])],
        ]);

        // normaliza e-mail para comparação
        $email = mb_strtolower(trim($data['email']));

        // tenta achar o usuário
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        // se não achou, volta com mensagem de erro
        if (! $user) {
            return back()
                ->withInput()
                ->with('error', 'Este e-mail não está cadastrado no sistema.');
        }

        // não deixar cadastrar o dono novamente
        if ($user->id === $project->owner_id) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'O dono já está no projeto como OWNER.');
        }

        // evitar duplicado
        if ($project->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'Usuário já é membro deste projeto.');
        }

        // adiciona membro com o papel selecionado
        $project->members()->attach($user->id, ['role' => $data['role']]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Membro adicionado.');
    }

    public function membersUpdate(Request $request, Project $project, User $user)
    {
        $this->authorizeUpdate($project);

        $data = $request->validate([
            'role' => ['required', \Illuminate\Validation\Rule::in(['PRODUCT_OWNER','SCRUM_MASTER','DEVELOPER'])],
        ]);

        if ($user->id === $project->owner_id) {
            return redirect()->route('projects.show', $project)
                ->with('error','O dono já é OWNER e não pode ter papel trocado.');
        }

        if (! $project->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('projects.show', $project)
                ->with('error','Usuário não é membro deste projeto.');
        }

        $project->members()->updateExistingPivot($user->id, ['role' => $data['role']]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Papel atualizado.');
    }

    public function membersDestroy(Project $project, User $user)
    {
        $this->authorizeUpdate($project);

        if ($user->id === $project->owner_id) {
            return redirect()->route('projects.show', $project)
                ->with('error','Não é possível remover o dono do projeto.');
        }

        $project->members()->detach($user->id);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Membro removido.');
    }

    private function authorizeView(Project $project): void
    {
        $isMember = $project->members()->where('user_id', auth()->id())->exists();
        abort_unless($isMember, 403);
    }

    private function authorizeUpdate(Project $project): void
    {
        abort_unless($project->owner_id === auth()->id(), 403);
    }
}
