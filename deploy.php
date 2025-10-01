<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'deploy/custom.php';

/*
 * BIOSPEX CI/CD DEPLOYMENT CONFIGURATION - Option 1 Implementation
 *
 * USAGE:
 * - Automatic deployment via GitHub Actions (recommended)
 * - Manual deployment: dep deploy production|development
 *
 * HOW IT WORKS:
 * 1. GitHub Actions builds assets and creates artifacts
 * 2. Deployer downloads artifacts (no server-side building)
 * 3. Queue-safe deployment (checks for active jobs)
 * 4. Environment-specific configuration
 * 5. Automatic cleanup (node_modules removed)
 */

// Deployment Configuration
set('repository', 'https://github.com/iDigBio/Biospex.git');
set('base_path', '/data/web');
set('remote_user', 'ubuntu');
set('php_fpm_version', '8.3');
set('ssh_multiplexing', true);
set('writable_mode', 'chmod');
set('keep_releases', 3);  // Keep only 3 recent releases
// Shared Files (persisted across deployments)
set('shared_files', [
    '.env',                        // Environment configuration
    'public/mix-manifest.json',    // Laravel Mix manifest for asset versioning
]);

// Shared Directories (persisted across deployments)
set('shared_dirs', [
    'storage',          // Laravel storage (logs, cache, uploads)
    'public/css',       // Compiled CSS files
    'public/js',        // Compiled JavaScript files
    'public/fonts',     // Web fonts
    'public/images',    // Static images
    'public/svg',       // SVG assets
    'public/vendor',    // Vendor assets (Nova, etc.)
]);

// Files/Directories to Remove After Deployment
set('clear_paths', [
    'node_modules',     // Remove after CI artifacts are deployed
]);

// Server Configurations
// Production: main branch → /data/web/biospex
host('production')
    ->set('hostname', '3.142.169.134')
    ->set('deploy_path', '{{base_path}}/biospex')
    ->set('branch', 'main')
    ->set('domain_name', 'biospex');

// Development: development branch → /data/web/dev.biospex
host('development')
    ->set('hostname', '3.142.169.134')
    ->set('deploy_path', '{{base_path}}/dev.biospex')
    ->set('branch', 'development')
    ->set('domain_name', 'dev-biospex');

/*
 * DEPLOYMENT TASK SEQUENCE - CI/CD Option 1 Implementation
 *
 * This sequence eliminates server-side building by using CI artifacts.
 * Each task is executed in order with proper error handling.
 */
desc('Deploys your project using CI/CD artifacts');
task('deploy', [
    // Phase 1: Preparation
    'deploy:prepare',           // Create release directory and setup structure
    // REMOVED: 'upload:env',   // Now handled by local Git pre-push hook! 🎉

    // Phase 2: Dependencies & Assets
    'deploy:vendors',          // Install PHP Composer dependencies (--no-dev --optimize-autoloader)
    'deploy:ci-artifacts',     // Download & extract pre-built assets from GitHub Actions (NEW: No server building!)

    // Phase 3: Laravel Setup
    'artisan:storage:link',    // Create symbolic link for storage directory
    'artisan:nova:publish',    // Publish Laravel Nova assets
    'artisan:app:deploy-files', // Custom app deployment files

    // Phase 4: Database & Updates
    'artisan:migrate',         // Run database migrations
    'artisan:app:update-queries', // Run custom database updates

    // Phase 5: Cache Optimization
    'artisan:optimize:clear',  // Clear all Laravel caches
    'artisan:cache:clear',     // Clear application cache
    'artisan:config:cache',    // Cache configuration files
    'artisan:route:cache',     // Cache route definitions
    'artisan:view:cache',      // Cache Blade templates
    'artisan:event:cache',     // Cache event listeners
    'artisan:optimize',        // Run Laravel optimization
    'artisan:filament:optimize',   // Optimize Filament resources and assets

    // Phase 6: OpCache Management (Production Only)
    'opcache:reset-production', // Reset OpCache after deployment (production only)

    // Phase 7: Domain-Specific Supervisor Management
    'supervisor:reload',               // Update configs only
    'supervisor:restart-domain-safe',  // Check queues + restart domain processes
    'set:permissions',
    'deploy:publish',
]);

// Hooks
after('deploy:failed', 'deploy:unlock');
