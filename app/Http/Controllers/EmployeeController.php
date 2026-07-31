<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index()
    {
        return response()->json(Employee::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $employee = Employee::create($request->all());
        return response()->json($employee, 201);
    }

    public function update(Request $request)
    {
        if (!$request->id) return response()->json(['error' => 'Employee ID required'], 400);
        $employee = Employee::find($request->id);
        if ($employee) {
            $employee->update($request->all());
            return response()->json($employee);
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function destroy(Request $request)
    {
        if (!$request->query('id')) return response()->json(['error' => 'Employee ID required'], 400);
        Employee::destroy($request->query('id'));
        return response()->json(['success' => true]);
    }
}
