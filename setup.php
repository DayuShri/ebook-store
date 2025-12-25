#!/usr/bin/env php
<?php

/**
 * E-Book Store Setup Script
 * 
 * This script automates the initial setup process for the E-Book Store application.
 * It will guide you through configuring the environment and setting up the database.
 */

echo "\n";
echo "╔════════════════════════════════════════╗\n";
echo "║   E-Book Store - Setup Wizard         ║\n";
echo "║   Initializing Application Setup...   ║\n";
echo "╚════════════════════════════════════════╝\n";
echo "\n";

// Check if running from project root
if (!file_exists('composer.json')) {
    die("❌ Error: Please run this script from the project root directory.\n");
}

echo "📋 Step 1: Checking requirements...\n";

// Check PHP version
$phpVersion = PHP_VERSION;
echo "   ✓ PHP Version: {$phpVersion}\n";

if (version_compare($phpVersion, '8.2.0', '<')) {
    die("   ❌ Error: PHP 8.2 or higher is required. You have {$phpVersion}\n");
}

// Check required PHP extensions
$requiredExtensions = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'curl', 'fileinfo'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "   ❌ Missing PHP extensions: " . implode(', ', $missingExtensions) . "\n";
    die("   Please install the missing extensions and try again.\n");
}

echo "   ✓ All required PHP extensions are installed\n";
echo "\n";

// Check if .env exists
if (file_exists('.env')) {
    echo "⚠️  .env file already exists.\n";
    echo "   Do you want to overwrite it? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'yes' && strtolower($line) !== 'y') {
        echo "   Skipping .env setup...\n";
        $skipEnv = true;
    } else {
        $skipEnv = false;
    }
} else {
    $skipEnv = false;
}

if (!$skipEnv) {
    echo "\n📋 Step 2: Setting up environment file...\n";
    
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "   ✓ .env file created from .env.example\n";
    } else {
        die("   ❌ Error: .env.example not found\n");
    }

    // Generate application key
    echo "\n📋 Step 3: Generating application key...\n";
    exec('php artisan key:generate', $output, $return);
    if ($return === 0) {
        echo "   ✓ Application key generated\n";
    } else {
        echo "   ⚠️  Warning: Could not generate key automatically\n";
        echo "   Please run: php artisan key:generate\n";
    }
} else {
    echo "\n📋 Step 2-3: Skipped (using existing .env)\n";
}

echo "\n📋 Step 4: Installing Composer dependencies...\n";
echo "   This may take a few minutes...\n";
exec('composer install --no-interaction', $output, $return);
if ($return === 0) {
    echo "   ✓ Composer dependencies installed\n";
} else {
    echo "   ⚠️  Warning: Composer install had issues\n";
    echo "   Please run manually: composer install\n";
}

echo "\n📋 Step 5: Installing NPM dependencies...\n";
echo "   This may take a few minutes...\n";
exec('npm install', $output, $return);
if ($return === 0) {
    echo "   ✓ NPM dependencies installed\n";
} else {
    echo "   ⚠️  Warning: NPM install had issues\n";
    echo "   Please run manually: npm install\n";
}

echo "\n📋 Step 6: Database Configuration\n";
echo "   Current database settings in .env:\n";

// Read current .env
$envContent = file_get_contents('.env');
preg_match('/DB_CONNECTION=(.*)/', $envContent, $dbConnection);
preg_match('/DB_DATABASE=(.*)/', $envContent, $dbName);
preg_match('/DB_USERNAME=(.*)/', $envContent, $dbUser);

echo "   - Connection: " . ($dbConnection[1] ?? 'not set') . "\n";
echo "   - Database: " . ($dbName[1] ?? 'not set') . "\n";
echo "   - Username: " . ($dbUser[1] ?? 'not set') . "\n";
echo "\n";

echo "   Do you want to run migrations now? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) === 'yes' || strtolower($line) === 'y') {
    echo "\n   Running migrations...\n";
    exec('php artisan migrate --force', $output, $return);
    if ($return === 0) {
        echo "   ✓ Database migrations completed\n";
        
        echo "\n   Do you want to seed demo data? (yes/no): ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($line) === 'yes' || strtolower($line) === 'y') {
            echo "   Seeding database...\n";
            exec('php artisan db:seed --class=CatalogSeeder', $output, $return);
            exec('php artisan db:seed --class=DemoSeeder', $output, $return2);
            
            if ($return === 0 && $return2 === 0) {
                echo "   ✓ Demo data seeded successfully\n";
            } else {
                echo "   ⚠️  Some seeders may have failed\n";
            }
        }
    } else {
        echo "   ❌ Migration failed. Please check your database configuration.\n";
    }
} else {
    echo "   Skipped migrations. You can run later with: php artisan migrate\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   ✅ Setup Complete!                                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "🚀 Next Steps:\n";
echo "\n";
echo "1. Update your .env file with:\n";
echo "   - Supabase credentials (for file storage)\n";
echo "   - Xendit API key (for payments)\n";
echo "\n";
echo "2. Start the development server:\n";
echo "   php artisan serve\n";
echo "\n";
echo "3. In another terminal, start the queue worker:\n";
echo "   php artisan queue:work\n";
echo "\n";
echo "4. (Optional) Start Vite dev server:\n";
echo "   npm run dev\n";
echo "\n";
echo "5. Visit: http://localhost:8000\n";
echo "\n";
echo "📚 Documentation:\n";
echo "   - Frontend Integration: FRONTEND-INTEGRATION-GUIDE.md\n";
echo "   - General Instructions: INSTRUCTION.md\n";
echo "   - Library Flow: LIBRARY-FLOW-DOCUMENTATION.md\n";
echo "\n";
echo "Happy coding! 🎉\n";
echo "\n";
