<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test projects
$request = Illuminate\Http\Request::create('/api/employee/projects', 'GET');
$response = app()->handle($request);
echo "Projects Status: " . $response->getStatusCode() . "\n";
echo "Projects Content: " . $response->getContent() . "\n";

// Test tasks
$request = Illuminate\Http\Request::create('/api/employee/tasks', 'GET');
$response = app()->handle($request);
echo "Tasks Status: " . $response->getStatusCode() . "\n";
echo "Tasks Content: " . $response->getContent() . "\n";

// Test overview
$request = Illuminate\Http\Request::create('/api/employee/overview-stats', 'GET');
$response = app()->handle($request);
echo "Overview Status: " . $response->getStatusCode() . "\n";
echo "Overview Content: " . $response->getContent() . "\n";
