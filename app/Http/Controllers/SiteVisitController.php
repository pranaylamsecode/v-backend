<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteVisit;

class SiteVisitController extends Controller
{
    public function index()
    {
        return response()->json(SiteVisit::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = SiteVisit::create($request->all());
        return response()->json($data, 201);
    }

    public function update(Request $request)
    {
        if (!$request->id) return response()->json(['error' => 'ID required'], 400);
        $data = SiteVisit::find($request->id);
        if ($data) {
            $data->update($request->all());
            return response()->json($data);
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function destroy(Request $request)
    {
        if (!$request->query('id')) return response()->json(['error' => 'ID required'], 400);
        SiteVisit::destroy($request->query('id'));
        return response()->json(['success' => true]);
    }
}