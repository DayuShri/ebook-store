<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerModuleRoutes();
    }

    protected function registerModuleRoutes(): void
    {
        $modulesPath = app_path('Modules');

        if (!is_dir($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {
            $routesPath = $module . '/Routes';

            // Load API routes
            if (file_exists($routesPath . '/api.php')) {
                Route::prefix('api/v1')
                    ->middleware('api')
                    ->group($routesPath . '/api.php');
            }

            // Load HMVC routes
            if (file_exists($routesPath . '/hmvc.php')) {
                Route::middleware('api')
                    ->group($routesPath . '/hmvc.php');
            }
        }
    }
}
