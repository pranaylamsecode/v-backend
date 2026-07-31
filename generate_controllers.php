<?php

function generateController($name, $model) {
    $content = <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\\$model;

class $name extends Controller
{
    public function index()
    {
        return response()->json($model::orderBy('created_at', 'desc')->get());
    }

    public function store(Request \$request)
    {
        \$data = $model::create(\$request->all());
        return response()->json(\$data, 201);
    }

    public function update(Request \$request)
    {
        if (!\$request->id) return response()->json(['error' => 'ID required'], 400);
        \$data = $model::find(\$request->id);
        if (\$data) {
            \$data->update(\$request->all());
            return response()->json(\$data);
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function destroy(Request \$request)
    {
        if (!\$request->query('id')) return response()->json(['error' => 'ID required'], 400);
        $model::destroy(\$request->query('id'));
        return response()->json(['success' => true]);
    }
}
PHP;
    file_put_contents("app/Http/Controllers/$name.php", $content);
}

generateController('AttendanceController', 'Attendance');
generateController('DBProjectController', 'DBProject');
generateController('DataItemController', 'DataItem');
generateController('SiteVisitController', 'SiteVisit');

$routes = <<<PHP
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

    Route::any('/employees', function (Request \$request) {
        \$controller = app(EmployeeController::class);
        return match (\$request->method()) {
            'GET' => \$controller->index(),
            'POST' => \$controller->store(\$request),
            'PUT' => \$controller->update(\$request),
            'DELETE' => \$controller->destroy(\$request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/projects', function (Request \$request) {
        \$controller = app(DBProjectController::class);
        return match (\$request->method()) {
            'GET' => \$controller->index(),
            'POST' => \$controller->store(\$request),
            'PUT' => \$controller->update(\$request),
            'DELETE' => \$controller->destroy(\$request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/attendance', function (Request \$request) {
        \$controller = app(AttendanceController::class);
        return match (\$request->method()) {
            'GET' => \$controller->index(),
            'POST' => \$controller->store(\$request),
            'PUT' => \$controller->update(\$request),
            'DELETE' => \$controller->destroy(\$request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/data', function (Request \$request) {
        \$controller = app(DataItemController::class);
        return match (\$request->method()) {
            'GET' => \$controller->index(),
            'POST' => \$controller->store(\$request),
            'PUT' => \$controller->update(\$request),
            'DELETE' => \$controller->destroy(\$request),
            default => response()->json(['error' => 'Method not allowed'], 405),
        };
    });

    Route::any('/stats', function (Request \$request) {
        // Mock stats or implement properly
        return response()->json(['message' => 'Stats endpoint not fully implemented yet']);
    });
});
PHP;

file_put_contents('routes/api.php', $routes);

// Since models need fillable
$models = ['Employee', 'Attendance', 'DBProject', 'DataItem', 'SiteVisit'];
foreach ($models as $model) {
    $path = "app/Models/$model.php";
    $content = file_get_contents($path);
    if (strpos($content, 'protected $guarded') === false) {
        $content = str_replace('{', "{\n    protected \$guarded = [];", $content);
        file_put_contents($path, $content);
    }
}

echo "Done\n";
