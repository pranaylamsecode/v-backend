<?php

// 1. Update migration
$migrationFile = glob('database/migrations/*add_profile_fields_to_employees_table.php')[0];
$migrationContent = file_get_contents($migrationFile);
$migrationContent = str_replace(
    '//',
    "\$table->string('photo_url')->nullable();\n            \$table->date('join_date')->nullable();",
    $migrationContent
);
$migrationContent = str_replace(
    "Schema::table('employees', function (Blueprint \$table) {\n            //\n        });",
    "Schema::table('employees', function (Blueprint \$table) {\n            \$table->dropColumn(['photo_url', 'join_date']);\n        });",
    $migrationContent
);
file_put_contents($migrationFile, $migrationContent);

// 2. Update EmployeeController
$ecPath = 'app/Http/Controllers/EmployeeController.php';
$ecContent = file_get_contents($ecPath);

// Add Hash facade and Storage facade
if (strpos($ecContent, 'use Illuminate\Support\Facades\Hash;') === false) {
    $ecContent = str_replace(
        'use App\Models\Employee;',
        "use App\Models\Employee;\nuse Illuminate\Support\Facades\Hash;\nuse Illuminate\Support\Facades\Storage;",
        $ecContent
    );
}

// Replace update method to handle file uploads and password hashing
$newUpdate = <<<PHP
    public function update(Request \$request)
    {
        if (!\$request->id) return response()->json(['error' => 'Employee ID required'], 400);
        \$employee = Employee::find(\$request->id);
        
        if (\$employee) {
            \$data = \$request->all();
            
            // Handle password reset
            if (!empty(\$data['password'])) {
                \$data['password'] = Hash::make(\$data['password']);
            } else {
                unset(\$data['password']);
            }
            
            // Handle photo upload
            if (\$request->hasFile('photo')) {
                // Delete old photo if exists
                if (\$employee->photo_url && Storage::disk('public')->exists(str_replace('/storage/', '', \$employee->photo_url))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', \$employee->photo_url));
                }
                
                \$path = \$request->file('photo')->store('employees', 'public');
                \$data['photo_url'] = '/storage/' . \$path;
            }

            \$employee->update(\$data);
            return response()->json(\$employee);
        }
        return response()->json(['error' => 'Not found'], 404);
    }
PHP;

$ecContent = preg_replace('/public function update\(Request \$request\)[\s\S]*?return response\(\)->json\(\[\'error\' => \'Not found\'\], 404\);\n    \}/', $newUpdate, $ecContent);
file_put_contents($ecPath, $ecContent);

echo "Backend files updated\n";
