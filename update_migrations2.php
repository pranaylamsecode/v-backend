<?php

$replace = function($file, $from, $to) {
    file_put_contents($file, str_replace($from, $to, file_get_contents($file)));
};

$search = '$table->id();' . "\n" . '            $table->timestamps();';

$replace('database/migrations/2026_07_31_082436_create_employee_tasks_table.php', $search,
    '$table->id();' . "\n" .
    '            $table->foreignId(\'employee_id\')->constrained()->cascadeOnDelete();' . "\n" .
    '            $table->string(\'title\');' . "\n" .
    '            $table->text(\'description\')->nullable();' . "\n" .
    '            $table->string(\'status\')->default(\'PENDING\');' . "\n" .
    '            $table->date(\'due_date\')->nullable();' . "\n" .
    '            $table->timestamps();'
);

$replace('database/migrations/2026_07_31_082437_create_leads_table.php', $search,
    '$table->id();' . "\n" .
    '            $table->string(\'name\');' . "\n" .
    '            $table->string(\'email\')->nullable();' . "\n" .
    '            $table->string(\'phone\')->nullable();' . "\n" .
    '            $table->string(\'company\')->nullable();' . "\n" .
    '            $table->string(\'status\')->default(\'NEW\');' . "\n" .
    '            $table->text(\'notes\')->nullable();' . "\n" .
    '            $table->timestamps();'
);

$replace('database/migrations/2026_07_31_082439_create_d_b_project_employee_table.php', $search,
    '$table->id();' . "\n" .
    '            $table->foreignId(\'d_b_project_id\')->constrained()->cascadeOnDelete();' . "\n" .
    '            $table->foreignId(\'employee_id\')->constrained()->cascadeOnDelete();' . "\n" .
    '            $table->timestamps();'
);

// Add details to projects table migration
$addDetailsSearch = 'public function up(): void' . "\n" . '    {' . "\n" . '        Schema::table(\'d_b_projects\', function (Blueprint $table) {' . "\n" . '            //' . "\n" . '        });';
$addDetailsReplace = 'public function up(): void' . "\n" . '    {' . "\n" . '        Schema::table(\'d_b_projects\', function (Blueprint $table) {' . "\n" . '            $table->text(\'description\')->nullable();' . "\n" . '            $table->string(\'repository_url\')->nullable();' . "\n" . '            $table->string(\'live_url\')->nullable();' . "\n" . '        });';
$dropDetailsSearch = 'public function down(): void' . "\n" . '    {' . "\n" . '        Schema::table(\'d_b_projects\', function (Blueprint $table) {' . "\n" . '            //' . "\n" . '        });';
$dropDetailsReplace = 'public function down(): void' . "\n" . '    {' . "\n" . '        Schema::table(\'d_b_projects\', function (Blueprint $table) {' . "\n" . '            $table->dropColumn([\'description\', \'repository_url\', \'live_url\']);' . "\n" . '        });';
$fileAddDetails = 'database/migrations/2026_07_31_082438_add_details_to_d_b_projects_table.php';
$replace($fileAddDetails, $addDetailsSearch, $addDetailsReplace);
$replace($fileAddDetails, $dropDetailsSearch, $dropDetailsReplace);

echo "Migrations updated successfully!\n";
