<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DBProject;

class DBProjectController extends Controller
{
        public function index()
    {
        $projects = \App\Models\DBProject::with('employees')->orderBy('created_at', 'desc')->get();
        return response()->json($projects);
    }

        public function store(Request $request)
    {
        $data = \App\Models\DBProject::create($request->except(['employee_ids', 'employees']));
        if ($request->has('employee_ids')) {
            $data->employees()->sync($request->employee_ids);
        }
        return response()->json($data->load('employees'), 201);
    }

        public function update(Request $request)
    {
        if (!$request->id) return response()->json(['error' => 'ID required'], 400);
        $data = \App\Models\DBProject::find($request->id);
        if ($data) {
            $data->update($request->except(['employee_ids', 'employees']));
            if ($request->has('employee_ids')) {
                $data->employees()->sync($request->employee_ids);
            }
            return response()->json($data->load('employees'));
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function destroy(Request $request)
    {
        if (!$request->query('id')) return response()->json(['error' => 'ID required'], 400);
        DBProject::destroy($request->query('id'));
        return response()->json(['success' => true]);
    }
}