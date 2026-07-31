<?php

// 1. Update migration
$migrationFile = glob('database/migrations/*add_password_to_employees_table.php')[0];
$migrationContent = file_get_contents($migrationFile);
$migrationContent = str_replace(
    '//',
    '$table->string(\'password\')->nullable();',
    $migrationContent
);
// replace down
$migrationContent = str_replace(
    'public function down(): void' . "\n" . '    {' . "\n" . '        Schema::table(\'employees\', function (Blueprint $table) {' . "\n" . '            $table->string(\'password\')->nullable();' . "\n" . '        });',
    'public function down(): void' . "\n" . '    {' . "\n" . '        Schema::table(\'employees\', function (Blueprint $table) {' . "\n" . '            $table->dropColumn(\'password\');' . "\n" . '        });',
    $migrationContent
);
file_put_contents($migrationFile, $migrationContent);

// 2. Update Employee model
$employeeModel = 'app/Models/Employee.php';
$employeeContent = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected \$guarded = [];

    protected \$hidden = [
        'password',
        'remember_token',
    ];

    public function tasks()
    {
        return \$this->hasMany(EmployeeTask::class);
    }
}
PHP;
file_put_contents($employeeModel, $employeeContent);

// 3. Update EmployeeController to set password
$employeeController = 'app/Http/Controllers/EmployeeController.php';
$ecContent = file_get_contents($employeeController);
$ecContent = str_replace(
    '$employee = Employee::create($request->all());',
    "\$data = \$request->all();\n        if (empty(\$data['password'])) {\n            \$data['password'] = \Illuminate\Support\Facades\Hash::make('password123');\n        }\n        \$employee = Employee::create(\$data);",
    $ecContent
);
file_put_contents($employeeController, $ecContent);

// 4. Create EmployeeAuthController
$eacContent = <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EmployeeAuthController extends Controller
{
    public function login(Request \$request)
    {
        \$request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        \$employee = Employee::where('email', \$request->email)->first();

        if (!\$employee || !Hash::check(\$request->password, \$employee->password)) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        \$token = \$employee->createToken('employee-token')->plainTextToken;
        \$cookie = cookie('employee_auth_token', \$token, 60 * 24 * 7, '/', null, false, true);

        return response()->json([
            'message' => 'Login successful',
            'user' => \$employee,
            'role' => 'employee'
        ])->withCookie(\$cookie);
    }

    public function logout(Request \$request)
    {
        \$request->user()->currentAccessToken()->delete();
        \$cookie = cookie()->forget('employee_auth_token');
        return response()->json(['message' => 'Logged out successfully'])->withCookie(\$cookie);
    }

    public function me(Request \$request)
    {
        return response()->json([
            'user' => \$request->user(),
            'role' => 'employee'
        ]);
    }
}
PHP;
file_put_contents('app/Http/Controllers/EmployeeAuthController.php', $eacContent);

// 5. Update api.php
$apiPath = 'routes/api.php';
$apiContent = file_get_contents($apiPath);
$apiContent = str_replace('use App\Http\Controllers\EmployeeTaskController;', "use App\Http\Controllers\EmployeeTaskController;\nuse App\Http\Controllers\EmployeeAuthController;", $apiContent);

$employeeRoutes = <<<PHP

    // Employee specific routes
    Route::post('/employee/login', [EmployeeAuthController::class, 'login']);
    
    // For employee dashboard, protected by sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/employee/logout', [EmployeeAuthController::class, 'logout']);
        Route::get('/employee/me', [EmployeeAuthController::class, 'me']);
        
        Route::get('/employee/tasks', function (Request \$request) {
            return response()->json(
                \App\Models\EmployeeTask::where('employee_id', \$request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
            );
        });
        
        Route::put('/employee/tasks', function (Request \$request) {
            \$task = \App\Models\EmployeeTask::where('id', \$request->id)
                ->where('employee_id', \$request->user()->id)
                ->first();
            if (\$task) {
                \$task->update(['status' => \$request->status]);
                return response()->json(\$task);
            }
            return response()->json(['error' => 'Not found or unauthorized'], 404);
        });

        Route::get('/employee/projects', function (Request \$request) {
            return response()->json(
                \$request->user()->projects // Note: requires projects relation on Employee
            );
        });
    });

PHP;

// Add relationship to Employee model for projects
$employeeModelContent = file_get_contents($employeeModel);
$employeeModelContent = str_replace(
    '}',
    "\n    public function projects()\n    {\n        return \$this->belongsToMany(DBProject::class, 'd_b_project_employee', 'employee_id', 'd_b_project_id');\n    }\n}",
    $employeeModelContent
);
file_put_contents($employeeModel, $employeeModelContent);

// Insert before stats
$apiContent = str_replace("Route::any('/stats',", $employeeRoutes . "\n    Route::any('/stats',", $apiContent);
file_put_contents($apiPath, $apiContent);

echo "Backend updated for employee portal!\n";
