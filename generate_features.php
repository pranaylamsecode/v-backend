<?php

$leadController = <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;

class LeadController extends Controller
{
    public function index()
    {
        return response()->json(Lead::orderBy('created_at', 'desc')->get());
    }

    public function store(Request \$request)
    {
        \$data = Lead::create(\$request->all());
        return response()->json(\$data, 201);
    }

    public function update(Request \$request)
    {
        if (!\$request->id) return response()->json(['error' => 'ID required'], 400);
        \$data = Lead::find(\$request->id);
        if (\$data) {
            \$data->update(\$request->all());
            return response()->json(\$data);
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function destroy(Request \$request)
    {
        if (!\$request->query('id')) return response()->json(['error' => 'ID required'], 400);
        Lead::destroy(\$request->query('id'));
        return response()->json(['success' => true]);
    }
}
PHP;

$taskController = <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeTask;

class EmployeeTaskController extends Controller
{
    public function index()
    {
        // Join with employee to get name
        \$tasks = EmployeeTask::join('employees', 'employees.id', '=', 'employee_tasks.employee_id')
            ->select('employee_tasks.*', 'employees.name as employee_name')
            ->orderBy('employee_tasks.created_at', 'desc')
            ->get();
        return response()->json(\$tasks);
    }

    public function store(Request \$request)
    {
        \$data = EmployeeTask::create(\$request->all());
        return response()->json(\$data, 201);
    }

    public function update(Request \$request)
    {
        if (!\$request->id) return response()->json(['error' => 'ID required'], 400);
        \$data = EmployeeTask::find(\$request->id);
        if (\$data) {
            \$data->update(\$request->all());
            return response()->json(\$data);
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function destroy(Request \$request)
    {
        if (!\$request->query('id')) return response()->json(['error' => 'ID required'], 400);
        EmployeeTask::destroy(\$request->query('id'));
        return response()->json(['success' => true]);
    }
}
PHP;

file_put_contents('app/Http/Controllers/LeadController.php', $leadController);
file_put_contents('app/Http/Controllers/EmployeeTaskController.php', $taskController);

// Update api.php
$apiPath = 'routes/api.php';
$apiContent = file_get_contents($apiPath);

$useStatements = "use App\Http\Controllers\SiteVisitController;\nuse App\Http\Controllers\LeadController;\nuse App\Http\Controllers\EmployeeTaskController;";
$apiContent = str_replace('use App\Http\Controllers\SiteVisitController;', $useStatements, $apiContent);

$newRoutes = <<<PHP
    Route::any('/leads', function (Request \$request) {
        \$controller = app(LeadController::class);
        return match (\$request->method()) {
            'GET' => \$controller->index(),
            'POST' => \$controller->store(\$request),
            'PUT' => \$controller->update(\$request),
            'DELETE' => \$controller->destroy(\$request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/tasks', function (Request \$request) {
        \$controller = app(EmployeeTaskController::class);
        return match (\$request->method()) {
            'GET' => \$controller->index(),
            'POST' => \$controller->store(\$request),
            'PUT' => \$controller->update(\$request),
            'DELETE' => \$controller->destroy(\$request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });
PHP;

// Insert new routes before the stats endpoint
$apiContent = str_replace('Route::any(\'/stats\',', $newRoutes . "\n\n    Route::any('/stats',", $apiContent);
file_put_contents($apiPath, $apiContent);

// Update DBProjectController to support assigning employees
$projectControllerPath = 'app/Http/Controllers/DBProjectController.php';
$projectContent = file_get_contents($projectControllerPath);
// Replace index
$newIndex = <<<PHP
    public function index()
    {
        \$projects = \App\Models\DBProject::with('employees')->orderBy('created_at', 'desc')->get();
        return response()->json(\$projects);
    }
PHP;
$projectContent = preg_replace('/public function index\(\)[\s\S]*?}/', $newIndex, $projectContent, 1);

// Replace store
$newStore = <<<PHP
    public function store(Request \$request)
    {
        \$data = \App\Models\DBProject::create(\$request->except('employee_ids'));
        if (\$request->has('employee_ids')) {
            \$data->employees()->sync(\$request->employee_ids);
        }
        return response()->json(\$data->load('employees'), 201);
    }
PHP;
$projectContent = preg_replace('/public function store\(Request \$request\)[\s\S]*?}/', $newStore, $projectContent, 1);

// Replace update
$newUpdate = <<<PHP
    public function update(Request \$request)
    {
        if (!\$request->id) return response()->json(['error' => 'ID required'], 400);
        \$data = \App\Models\DBProject::find(\$request->id);
        if (\$data) {
            \$data->update(\$request->except('employee_ids'));
            if (\$request->has('employee_ids')) {
                \$data->employees()->sync(\$request->employee_ids);
            }
            return response()->json(\$data->load('employees'));
        }
        return response()->json(['error' => 'Not found'], 404);
    }
PHP;
$projectContent = preg_replace('/public function update\(Request \$request\)[\s\S]*?}/', $newUpdate, $projectContent, 1);

file_put_contents($projectControllerPath, $projectContent);

// Update DBProject model to define the employees relationship
$dbProjectModel = 'app/Models/DBProject.php';
$dbProjectContent = file_get_contents($dbProjectModel);
if (strpos($dbProjectContent, 'public function employees()') === false) {
    $relationship = <<<PHP
    public function employees()
    {
        return \$this->belongsToMany(Employee::class, 'd_b_project_employee', 'd_b_project_id', 'employee_id');
    }
PHP;
    $dbProjectContent = str_replace('}', $relationship . "\n}", $dbProjectContent);
    file_put_contents($dbProjectModel, $dbProjectContent);
}

// Update Employee model to define the tasks relationship
$employeeModel = 'app/Models/Employee.php';
$employeeContent = file_get_contents($employeeModel);
if (strpos($employeeContent, 'public function tasks()') === false) {
    $relationship = <<<PHP
    public function tasks()
    {
        return \$this->hasMany(EmployeeTask::class);
    }
PHP;
    $employeeContent = str_replace('}', $relationship . "\n}", $employeeContent);
    file_put_contents($employeeModel, $employeeContent);
}

echo "Controllers and Routes generated!\n";
