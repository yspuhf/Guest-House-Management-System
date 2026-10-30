<?php

define('LARAVEL_START', microtime(true));

// If Laravel framework is not installed in vendor/, safely serve the interactive portal
// to prevent any 500 Server Error on shared hosting.
if (!file_exists(__DIR__.'/../vendor/laravel/framework')) {
    if (file_exists(__DIR__.'/index.html')) {
        include __DIR__.'/index.html';
        exit;
    } elseif (file_exists(__DIR__.'/../index.html')) {
        include __DIR__.'/../index.html';
        exit;
    }
}

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require_once __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
