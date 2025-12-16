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

    /**
     * Register routes from all modules.
     * 
     * This will automatically load routes from:
     * - app/Modules/{Module}/Routes/api.php -> Prefixed with /api/v1
     * - app/Modules/{Module}/Routes/hmvc.php -> NO auto-prefix (must define prefix manually in file)
     */
    protected function registerModuleRoutes(): void
    {
        $modulesPath = app_path('Modules');

        if (!is_dir($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {
            $routesPath = $module . '/Routes';

            // Load API routes with /api/v1 prefix
            if (file_exists($routesPath . '/api.php')) {
                Route::prefix('api/v1')
                    ->middleware('api')
                    ->group($routesPath . '/api.php');
            }

            // Load HMVC routes WITHOUT auto-prefix
            // Note: You must define your own prefix in the hmvc.php file (e.g., 'hmvc/wallet')
            if (file_exists($routesPath . '/hmvc.php')) {
                Route::middleware('api')
                    ->group($routesPath . '/hmvc.php');
            }
        }
    }
}
