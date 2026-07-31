<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DBProjectController;
use App\Http\Controllers\DataItemController;
use App\Http\Controllers\SiteVisitController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::any('/employees', function (Request $request) {
        $controller = app(EmployeeController::class);
        return match ($request->method()) {
            'GET' => $controller->index(),
            'POST' => $controller->store($request),
            'PUT' => $controller->update($request),
            'DELETE' => $controller->destroy($request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/projects', function (Request $request) {
        $controller = app(DBProjectController::class);
        return match ($request->method()) {
            'GET' => $controller->index(),
            'POST' => $controller->store($request),
            'PUT' => $controller->update($request),
            'DELETE' => $controller->destroy($request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/attendance', function (Request $request) {
        $controller = app(AttendanceController::class);
        return match ($request->method()) {
            'GET' => $controller->index(),
            'POST' => $controller->store($request),
            'PUT' => $controller->update($request),
            'DELETE' => $controller->destroy($request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/data', function (Request $request) {
        $controller = app(DataItemController::class);
        return match ($request->method()) {
            'GET' => $controller->index(),
            'POST' => $controller->store($request),
            'PUT' => $controller->update($request),
            'DELETE' => $controller->destroy($request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/stats', function (Request $request) {
        // Mock stats or implement properly
        return response()->json(['message' => 'Stats endpoint not fully implemented yet']);
    });
});