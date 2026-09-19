<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// This public_html folder sits beside the Laravel application directory.
// Rename ../onje-casa if your Hostinger application folder has another name.
$laravelRoot = __DIR__.'/../onje-casa';

// Determine if the application is in maintenance mode.
if (file_exists($maintenance = $laravelRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader.
require $laravelRoot.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request.
(require_once $laravelRoot.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
