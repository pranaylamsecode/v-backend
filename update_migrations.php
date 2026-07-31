<?php

$replace = function($file, $from, $to) {
    file_put_contents($file, str_replace($from, $to, file_get_contents($file)));
};

$search = '$table->id();' . "\n" . '            $table->timestamps();';

$replace('database/migrations/2026_07_31_065714_create_employees_table.php', $search, 
    '$table->id();' . "\n" .
    '            $table->string(\'name\');' . "\n" .
    '            $table->string(\'email\')->unique();' . "\n" .
    '            $table->string(\'position\');' . "\n" .
    '            $table->string(\'phone\')->nullable();' . "\n" .
    '            $table->string(\'status\')->default(\'ACTIVE\');' . "\n" .
    '            $table->timestamps();'
);

$replace('database/migrations/2026_07_31_065715_create_attendances_table.php', $search,
    '$table->id();' . "\n" .
    '            $table->foreignId(\'employeeId\')->constrained(\'employees\')->cascadeOnDelete();' . "\n" .
    '            $table->dateTime(\'date\')->useCurrent();' . "\n" .
    '            $table->dateTime(\'checkIn\');' . "\n" .
    '            $table->dateTime(\'checkOut\')->nullable();' . "\n" .
    '            $table->string(\'status\')->default(\'PRESENT\');' . "\n" .
    '            $table->timestamps();'
);

$replace('database/migrations/2026_07_31_065716_create_d_b_projects_table.php', $search,
    '$table->id();' . "\n" .
    '            $table->string(\'title\');' . "\n" .
    '            $table->string(\'client\');' . "\n" .
    '            $table->string(\'status\')->default(\'PENDING\');' . "\n" .
    '            $table->dateTime(\'deadline\')->nullable();' . "\n" .
    '            $table->integer(\'progress\')->default(0);' . "\n" .
    '            $table->timestamps();'
);

$replace('database/migrations/2026_07_31_065717_create_data_items_table.php', $search,
    '$table->id();' . "\n" .
    '            $table->string(\'title\');' . "\n" .
    '            $table->text(\'description\')->nullable();' . "\n" .
    '            $table->timestamps();'
);

$replace('database/migrations/2026_07_31_065718_create_site_visits_table.php', $search,
    '$table->id();' . "\n" .
    '            $table->date(\'date\')->useCurrent();' . "\n" .
    '            $table->integer(\'visits\')->default(1);' . "\n" .
    '            $table->string(\'uniqueIp\')->nullable();' . "\n" .
    '            $table->timestamps();'
);

echo "Done\n";
