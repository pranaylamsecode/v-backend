<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeTask;

class EmployeeTaskController extends Controller
{
    public function index()
    {
        // Join with employee to get name
        $tasks = EmployeeTask::join('employees', 'employees.id', '=', 'employee_tasks.employee_id')
            ->select('employee_tasks.*', 'employees.name as employee_name')
            ->orderBy('employee_tasks.created_at', 'desc')
            ->get();
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $data = EmployeeTask::create($request->all());
        return response()->json($data, 201);
    }

    public function update(Request $request)
    {
        if (!$request->id) return response()->json(['error' => 'ID required'], 400);
        $data = EmployeeTask::find($request->id);
        if ($data) {
            $data->update($request->all());
            return response()->json($data);
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function destroy(Request $request)
    {
        if (!$request->query('id')) return response()->json(['error' => 'ID required'], 400);
        EmployeeTask::destroy($request->query('id'));
        return response()->json(['success' => true]);
    }
}