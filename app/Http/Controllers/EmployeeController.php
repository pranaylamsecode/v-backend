<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        return response()->json(Employee::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (empty($data['password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make('password123');
        }
        $employee = Employee::create($data);
        return response()->json($employee, 201);
    }

        public function update(Request $request)
    {
        if (!$request->id) return response()->json(['error' => 'Employee ID required'], 400);
        $employee = Employee::find($request->id);
        
        if ($employee) {
            $data = $request->except(['_method', 'photo', 'id']);
            
            // Handle password reset
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($employee->photo_url && Storage::disk('public')->exists(str_replace('/storage/', '', $employee->photo_url))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $employee->photo_url));
                }
                
                $path = $request->file('photo')->store('employees', 'public');
                $data['photo_url'] = '/storage/' . $path;
            }

            $employee->update($data);
            return response()->json($employee->fresh());
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
