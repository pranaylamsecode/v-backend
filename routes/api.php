<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DBProjectController;
use App\Http\Controllers\DataItemController;
use App\Http\Controllers\SiteVisitController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\EmployeeTaskController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\WorkLogController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::any('/employees', function (Request $request) {
        $controller = app(EmployeeController::class);
        $method = $request->method();
        
        // Handle _method override for multipart/form-data (file uploads send as POST with _method=PUT)
        if ($method === 'POST' && $request->input('_method') === 'PUT') {
            return $controller->update($request);
        }
        
        return match ($method) {
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

        Route::any('/leads', function (Request $request) {
        $controller = app(LeadController::class);
        return match ($request->method()) {
            'GET' => $controller->index(),
            'POST' => $controller->store($request),
            'PUT' => $controller->update($request),
            'DELETE' => $controller->destroy($request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/tasks', function (Request $request) {
        $controller = app(EmployeeTaskController::class);
        return match ($request->method()) {
            'GET' => $controller->index(),
            'POST' => $controller->store($request),
            'PUT' => $controller->update($request),
            'DELETE' => $controller->destroy($request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    
    // Employee specific routes
    Route::post('/employee/login', [EmployeeAuthController::class, 'login']);
    
    // For employee dashboard, protected by sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/employee/logout', [EmployeeAuthController::class, 'logout']);
        Route::get('/employee/me', [EmployeeAuthController::class, 'me']);
        
        Route::get('/employee/tasks', function (Request $request) {
            return response()->json(
                \App\Models\EmployeeTask::where('employee_id', $request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
            );
        });
        
        Route::put('/employee/tasks', function (Request $request) {
            $task = \App\Models\EmployeeTask::where('id', $request->id)
                ->where('employee_id', $request->user()->id)
                ->first();
            if ($task) {
                $task->update(['status' => $request->status]);
                return response()->json($task);
            }
            return response()->json(['error' => 'Not found or unauthorized'], 404);
        });

        Route::get('/employee/projects', function (Request $request) {
            return response()->json(
                $request->user()->projects // Note: requires projects relation on Employee
            );
        });

        Route::get('/employee/work-logs', [WorkLogController::class, 'employeeIndex']);
        Route::post('/employee/work-logs', [WorkLogController::class, 'store']);
        
        Route::get('/employee/overview-stats', function (Request $request) {
            $user = $request->user();
            
            $pendingTasks = \App\Models\EmployeeTask::where('employee_id', $user->id)
                ->where('status', 'PENDING')
                ->count();
                
            $activeProjects = $user->projects()->where('status', 'IN_PROGRESS')->count();
            
            $hoursLoggedThisWeek = \App\Models\WorkLog::where('employee_id', $user->id)
                ->where('date', '>=', now()->startOfWeek())
                ->sum('hours');
                
            $recentTasks = \App\Models\EmployeeTask::where('employee_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
                
            $recentLogs = \App\Models\WorkLog::with(['project', 'task'])
                ->where('employee_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
                
            return response()->json([
                'pendingTasks' => $pendingTasks,
                'activeProjects' => $activeProjects,
                'hoursLoggedThisWeek' => $hoursLoggedThisWeek,
                'recentTasks' => $recentTasks,
                'recentLogs' => $recentLogs
            ]);
        });
    });

    Route::get('/work-logs', [WorkLogController::class, 'index']);

        Route::any('/stats', function (Request $request) {
        $employees = \App\Models\Employee::count();
        $activeProjectsCount = \App\Models\DBProject::where('status', 'IN_PROGRESS')->count();
        $pendingProjectsCount = \App\Models\DBProject::where('status', 'PENDING')->count();
        $totalLeads = \App\Models\Lead::count();

        // Project Status Distribution
        $projectsByStatus = \App\Models\DBProject::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();
            
        // Lead Status Distribution
        $leadsByStatus = \App\Models\Lead::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        // Get top 5 performers (by total completed tasks)
        $topPerformers = \App\Models\Employee::withCount(['tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'COMPLETED');
            }])
            ->orderByDesc('completed_tasks_count')
            ->take(5)
            ->get(['id', 'name', 'position', 'photo_url']);

        // Get tasks grouped by employee (for progress tracking)
        $employeeTasks = \App\Models\Employee::with('tasks')->get()->map(function ($emp) {
            $tasks = $emp->tasks;
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'position' => $emp->position,
                'total_tasks' => $tasks->count(),
                'pending' => $tasks->where('status', 'PENDING')->count(),
                'in_progress' => $tasks->where('status', 'IN_PROGRESS')->count(),
                'completed' => $tasks->where('status', 'COMPLETED')->count(),
            ];
        });

        // Get latest 5 tasks across company
        $recentTasks = \App\Models\EmployeeTask::with('employee:id,name')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'employees' => $employees,
            'activeProjectsCount' => $activeProjectsCount,
            'pendingProjectsCount' => $pendingProjectsCount,
            'totalLeads' => $totalLeads,
            'projectsByStatus' => $projectsByStatus,
            'leadsByStatus' => $leadsByStatus,
            'topPerformers' => $topPerformers,
            'employeeTasks' => $employeeTasks,
            'recentTasks' => $recentTasks
        ]);
    });

    Route::get('/reports', function (Request $request) {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));
        
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $employees = \App\Models\Employee::all();
        
        $reports = $employees->map(function ($emp) use ($startDate, $endDate) {
            // Tasks completed in this month
            $completedTasks = \App\Models\EmployeeTask::where('employee_id', $emp->id)
                ->where('status', 'COMPLETED')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();
                
            // Total hours logged in this month
            $hoursLogged = \App\Models\WorkLog::where('employee_id', $emp->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('hours');
                
            // Attendance stats for this month
            $attendances = \App\Models\Attendance::where('employeeId', $emp->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
                
            $daysPresent = $attendances->where('status', 'PRESENT')->count();
            $daysAbsent = $attendances->where('status', 'ABSENT')->count();
            $daysLeave = $attendances->where('status', 'LEAVE')->count();

            return [
                'employee_id' => $emp->id,
                'employee_name' => $emp->name,
                'employee_position' => $emp->position,
                'completed_tasks' => $completedTasks,
                'hours_logged' => $hoursLogged,
                'days_present' => $daysPresent,
                'days_absent' => $daysAbsent,
                'days_leave' => $daysLeave,
            ];
        });

        return response()->json([
            'month' => $month,
            'year' => $year,
            'data' => $reports
        ]);
    });
});