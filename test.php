<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $user = App\Models\Employee::first();
    echo "User: " . ($user ? $user->id : 'none') . "\n";

    if ($user) {
        $activeProjects = $user->projects()->where('status', 'IN_PROGRESS')->count();
        echo "Active Projects: $activeProjects\n";

        $hoursLoggedThisWeek = \App\Models\WorkLog::where('employee_id', $user->id)
            ->where('date', '>=', now()->startOfWeek())
            ->sum('hours');
        echo "Hours: $hoursLoggedThisWeek\n";

        $recentTasks = \App\Models\EmployeeTask::where('employee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        echo "Tasks count: " . $recentTasks->count() . "\n";

        $recentLogs = \App\Models\WorkLog::with(['project', 'task'])
            ->where('employee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        echo "Logs count: " . $recentLogs->count() . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
