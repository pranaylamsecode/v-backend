<?php

$apiPath = 'routes/api.php';
$apiContent = file_get_contents($apiPath);

$newStatsEndpoint = <<<PHP
    Route::any('/stats', function (Request \$request) {
        \$employees = \App\Models\Employee::count();
        \$activeProjectsCount = \App\Models\DBProject::where('status', 'IN_PROGRESS')->count();
        \$pendingProjectsCount = \App\Models\DBProject::where('status', 'PENDING')->count();
        \$totalLeads = \App\Models\Lead::count();

        // Get tasks grouped by employee
        \$employeeTasks = \App\Models\Employee::with('tasks')->get()->map(function (\$emp) {
            \$tasks = \$emp->tasks;
            return [
                'id' => \$emp->id,
                'name' => \$emp->name,
                'position' => \$emp->position,
                'total_tasks' => \$tasks->count(),
                'pending' => \$tasks->where('status', 'PENDING')->count(),
                'in_progress' => \$tasks->where('status', 'IN_PROGRESS')->count(),
                'completed' => \$tasks->where('status', 'COMPLETED')->count(),
            ];
        });

        // Get latest 5 tasks across company
        \$recentTasks = \App\Models\EmployeeTask::with('employee:id,name')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'employees' => \$employees,
            'activeProjectsCount' => \$activeProjectsCount,
            'pendingProjectsCount' => \$pendingProjectsCount,
            'totalLeads' => \$totalLeads,
            'employeeTasks' => \$employeeTasks,
            'recentTasks' => \$recentTasks,
            'totalVisits' => rand(100, 500) // Mocking this since no visitor tracking table exists
        ]);
    });
PHP;

$apiContent = preg_replace("/Route::any\('\/stats', function \(Request \\\$request\) \{[\s\S]*?\}\);/", $newStatsEndpoint, $apiContent);
file_put_contents($apiPath, $apiContent);

// Add employee relationship to EmployeeTask model
$employeeTaskModel = 'app/Models/EmployeeTask.php';
$etContent = file_get_contents($employeeTaskModel);
if (strpos($etContent, 'public function employee()') === false) {
    $etContent = str_replace(
        '}',
        "\n    public function employee()\n    {\n        return \$this->belongsTo(Employee::class);\n    }\n}",
        $etContent
    );
    file_put_contents($employeeTaskModel, $etContent);
}

echo "API updated\n";
