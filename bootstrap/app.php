<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
            'superadmin' => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckMaintenanceFlag::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('reports:send-monthly-admin')
            ->monthlyOn(1, '08:00')
            ->withoutOverlapping();

        $schedule->command('pos:sync-offline-sales')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->when(fn () => config('offline_pos.mode') === 'local');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$publicPath = env('APP_PUBLIC_PATH');
if (! $publicPath && ! empty($_SERVER['DOCUMENT_ROOT']) && is_file($_SERVER['DOCUMENT_ROOT'] . '/index.php')) {
    $publicPath = $_SERVER['DOCUMENT_ROOT'];
}

if ($publicPath) {
    $app->usePublicPath($publicPath);
}

return $app;

    /*
    |--------------------------------------------------------------------------
    | Create The Application
    |--------------------------------------------------------------------------
    |
    | The first thing we will do is create a new Laravel application instance
    | which serves as the "glue" for all the components of Laravel, and is
    | the IoC container for the system binding all of the various parts.
    |
    */
    
    $app = new Illuminate\Foundation\Application(
        $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
    );
    
    /*
    |--------------------------------------------------------------------------
    | Bind Important Interfaces
    |--------------------------------------------------------------------------
    |
    | Next, we need to bind some important interfaces into the container so
    | we will be able to resolve them when needed. The kernels serve the
    | incoming requests to this application from both the web and CLI.
    |
    */
    
    $app->singleton(
        Illuminate\Contracts\Http\Kernel::class,
        App\Http\Kernel::class
    );
    
    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );
    
    $app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class,
        App\Exceptions\Handler::class
    );
    
    /*
    |--------------------------------------------------------------------------
    | Return The Application
    |--------------------------------------------------------------------------
    |
    | This script returns the application instance. The instance is given to
    | the calling script so we can separate the building of the instances
    | from the actual running of the application and sending responses.
    |
    */
    
    return $app;
    
