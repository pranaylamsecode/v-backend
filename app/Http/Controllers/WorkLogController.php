<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WorkLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = \App\Models\WorkLog::with(['employee', 'project', 'task'])->orderBy('date', 'desc')->get();
        return response()->json($logs);
    }

    public function employeeIndex(Request $request)
    {
        $employee_id = $request->user()->id;

        $logs = \App\Models\WorkLog::with(['project', 'task'])
            ->where('employee_id', $employee_id)
            ->orderBy('date', 'desc')
            ->get();
            
        return response()->json($logs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'd_b_project_id' => 'nullable|exists:d_b_projects,id',
            'employee_task_id' => 'nullable|exists:employee_tasks,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'required|string',
        ]);

        $validated['employee_id'] = $request->user()->id;

        $log = \App\Models\WorkLog::create($validated);
        
        return response()->json($log->load(['project', 'task']), 201);
    }
}
