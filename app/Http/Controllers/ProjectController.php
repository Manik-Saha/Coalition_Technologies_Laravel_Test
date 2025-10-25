<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Project::create(['name' => $request->input('name')]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($project, 201);
        }

        return redirect()->back();
    }
}
