<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $projects = \App\Models\Project::orderBy('name')->get();
        $selectedProject = $request->query('project');

        if (!$selectedProject && $projects->isNotEmpty()) {
            $selectedProject = $projects->first()->id;
        }

        if ($selectedProject) {
            $tasks = Task::where('project_id', $selectedProject)->get();
        } else {
            $tasks = collect();
        }

        return view('tasks.index', compact('tasks', 'projects', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $projectId = $request->input('project_id');

        // determine max priority within the project (or globally if no project)
        $maxQuery = Task::when($projectId, function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });

        $max = $maxQuery->max('priority');
        $priority = $max ? $max + 1 : 1;

        $task = Task::create([
            'name' => $request->input('name'),
            'priority' => $priority,
            'project_id' => $projectId,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($task, 201);
        }

        return redirect()->route('tasks.index', ['project' => $projectId]);
    }

    public function update(Request $request, Task $task)
    {
        $request->validate(["name" => 'required|string|max:255']);
        $task->update(["name" => $request->input('name')]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($task);
        }

        return redirect()->route('tasks.index');
    }

    public function destroy(Request $request, Task $task)
    {
        DB::transaction(function () use ($task) {
            $projectId = $task->project_id;
            $task->delete();

            // Re-normalize priorities within the same project so they remain consecutive starting at 1
            $rows = Task::when($projectId, function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })->orderBy('priority')->get();

            $i = 1;
            foreach ($rows as $r) {
                if ($r->priority !== $i) {
                    $r->priority = $i;
                    $r->save();
                }
                $i++;
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('tasks.index');
    }

    /**
     * Accepts JSON { order: [id1, id2, ...] } and updates priorities so that
     * the first id gets priority 1, next priority 2, etc.
     */
    public function reorder(Request $request)
    {
        $order = $request->input('order');
        if (!is_array($order)) {
            return response()->json(['message' => 'Invalid payload'], 422);
        }

        DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                Task::where('id', $id)->update(['priority' => $index + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
