<?php

// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $isMember = $project->members()->where('user_id', auth()->id())->exists();
        abort_unless($isMember, 403);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['required', 'in:BAIXA,MÉDIA,ALTA'],
            'status'      => ['nullable', 'in:OPEN,IN_PROGRESS,TEST,DONE'],
            'due_date'    => ['nullable', 'date'],
            'assignee_id' => ['nullable', 'exists:users,id'],
        ]);

        if (!empty($data['assignee_id'])) {
            $isAssigneeMember = $project->members()
                ->where('user_id', $data['assignee_id'])
                ->exists();

            if (! $isAssigneeMember) {
                return redirect()
                    ->route('projects.show', $project)
                    ->with('error', 'Responsável precisa ser membro do projeto.');
            }
        }

        $task = Task::create([
            'project_id'  => $project->id,
            'creator_id'  => auth()->id(),
            'assignee_id' => $data['assignee_id'] ?? null,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'priority'    => $data['priority'],
            'status'      => $data['status'] ?? 'OPEN',
            'due_date'    => $data['due_date'] ?? null,
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Tarefa criada com sucesso.');
    }

    public function show(Task $task)
    {
        $project = $task->project;
        $isMember = $project->members()
            ->where('user_id', auth()->id())
            ->exists();

        abort_unless($isMember, 403);

        $task->load('project', 'assignee');

        return view('tasks.show', compact('task', 'project'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $project = $task->project;

        $isMember = $project->members()
            ->where('user_id', auth()->id())
            ->exists();

        abort_unless($isMember, 403);

        $data = $request->validate([
            'status' => ['required', 'in:OPEN,IN_PROGRESS,TEST,DONE'],
        ]);

        $task->update([
            'status' => $data['status'],
        ]);

        return redirect()
            ->back()
            ->with('success', 'Status da tarefa atualizado para: ' . $data['status']);
    }
}
