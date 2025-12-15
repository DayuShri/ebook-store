#!/usr/bin/env php
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Route Check ===\n\n";

$routes = Route::getRoutes();

echo "Checking for wallet routes...\n\n";

$walletRoutes = [];
foreach ($routes as $route) {
    $uri = $route->uri();
    if (str_contains($uri, 'wallet')) {
        $walletRoutes[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $uri,
            'action' => $route->getActionName(),
        ];
    }
}

if (empty($walletRoutes)) {
    echo "❌ No wallet routes found!\n";
    echo "\nDebugging:\n";
    echo "- ModuleServiceProvider registered: " . (class_exists('App\Providers\ModuleServiceProvider') ? 'YES' : 'NO') . "\n";
    echo "- Payment module exists: " . (is_dir(app_path('Modules/Payment')) ? 'YES' : 'NO') . "\n";
    echo "- API routes file exists: " . (file_exists(app_path('Modules/Payment/Routes/api.php')) ? 'YES' : 'NO') . "\n";
    echo "- HMVC routes file exists: " . (file_exists(app_path('Modules/Payment/Routes/hmvc.php')) ? 'YES' : 'NO') . "\n";
} else {
    echo "✅ Found " . count($walletRoutes) . " wallet routes:\n\n";
    foreach ($walletRoutes as $route) {
        echo "  {$route['method']} {$route['uri']}\n";
        echo "    → {$route['action']}\n\n";
    }
}

echo "\n=== Check Complete ===\n";
