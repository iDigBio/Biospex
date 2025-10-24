<?php

/*
 * BIOSPEX CUSTOM DEPLOYMENT TASKS - Option 1 Implementation
 *
 * This file contains custom deployment tasks for the Biospex project.
 *
 * KEY FEATURES:
 * - CI/CD artifact deployment (no server-side building)
 * - Environment-specific configuration uploads
 * - Queue-safe deployments with active job checking
 * - Supervisor management for background processes
 * - Custom Laravel Artisan commands integration
 *
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Deployer;

/*
 * =============================================================================
 * COMPOSER DEPENDENCY MANAGEMENT - SAFE INSTALLATION
 * =============================================================================
 */

use Exception;

desc('Install Composer dependencies safely (with --no-scripts to prevent database connection issues)');
task('deploy:vendors', function () {
    if (! test('[ -f {{release_path}}/composer.json ]')) {
        return;
    }

    // Install dependencies without running scripts to prevent database connection issues
    run('cd {{release_path}} && {{bin/composer}} install --prefer-dist --no-progress --no-suggest --no-dev --optimize-autoloader --no-scripts');

    writeln('✅ Composer dependencies installed safely (without scripts)');
});

desc('Run Laravel package discovery after environment is ready');
task('artisan:package:discover', function () {
    cd('{{release_or_current_path}}');
    run('php artisan package:discover --ansi');
    writeln('✅ Laravel package discovery completed');
});

/*
 * =============================================================================
 * CUSTOM LARAVEL ARTISAN TASKS
 * =============================================================================
 */

desc('Running custom database update queries');
task('artisan:app:update-queries', function () {
    cd('{{release_or_current_path}}');
    run('php artisan app:update-queries');  // Custom command for database schema updates
});

desc('Deploying application-specific files and configurations');
task('artisan:app:deploy-files', function () {
    cd('{{release_or_current_path}}');
    run('php artisan app:deploy-files');    // Custom command for file deployments
});

/**
 * Publish Filament assets
 */
desc('Publish Filament assets');
task('artisan:filament:assets', function () {
    cd('{{release_or_current_path}}');
    run('php artisan filament:assets');
    writeln('✅ Filament assets published');
});

desc('Optimize Filament resources and assets');
task('artisan:filament:optimize', function () {
    cd('{{release_or_current_path}}');
    run('php artisan filament:optimize --ansi');
    writeln('✅ Filament optimization completed');
});

/*
 * =============================================================================
 * FILE SYSTEM & PERMISSIONS MANAGEMENT
 * =============================================================================
 */

desc('Setting proper file permissions and clearing logs');
task('set:permissions', function () {
    // Set ownership: ubuntu user, www-data group for web server access
    run('sudo chown -R ubuntu.www-data {{deploy_path}}');

    // Clear all log files to prevent disk space issues
    run('sudo truncate -s 0 {{release_or_current_path}}/storage/logs/*.log');
});

/*
 * =============================================================================
 * SUPERVISOR PROCESS MANAGEMENT
 * =============================================================================
 */

desc('Reload Supervisor configuration (config-only update)');
task('supervisor:reload', function () {
    run('sudo supervisorctl reread');
    run('sudo supervisorctl update');
});

desc('Safely restart domain-specific supervisor processes (checks queues first)');
task('supervisor:restart-domain-safe', function () {
    $domain = get('domain_name');

    if (! $domain) {
        throw new Exception('Domain name not configured for this host');
    }

    // Check critical queues first
    $configOutput = run('php {{release_or_current_path}}/artisan tinker --execute="echo json_encode(config(\'queue.monitored_queues\', [\'export\', \'geolocate\', \'import\', \'ocr\', \'lambda_ocr\', \'reconcile\', \'sns_image_export\', \'sns_reconciliation\', \'sns_tesseract_ocr\', \'sernec_file\', \'sernec_row\']));"');
    $queues = json_decode(trim($configOutput), true) ?: ['export', 'geolocate', 'import', 'ocr', 'lambda_ocr', 'reconcile', 'sns_image_export', 'sns_reconciliation', 'sns_tesseract_ocr', 'sernec_file', 'sernec_row'];

    foreach ($queues as $queue) {
        if (empty($queue)) {
            continue;
        }
        $count = run("php {{release_or_current_path}}/artisan queue:count {$queue} --quiet || echo 0", ['tty' => false]);
        if ((int) trim($count) > 0) {
            writeln("⚠️ Queue '{$queue}' has active jobs. Skipping supervisor restart.");

            return;
        }
    }

    // Safe to restart domain processes
    run("sudo supervisorctl restart {$domain}:*");
});

/*
 * =============================================================================
 * CI/CD ARTIFACT DEPLOYMENT - CORE OF OPTION 1 IMPLEMENTATION
 * =============================================================================
 */

desc('Download and extract pre-built assets from GitHub Actions (OPTION 1 CORE FEATURE)');
task('deploy:ci-artifacts', function () {
    // Environment variables automatically provided by GitHub Actions workflow
    // Try multiple methods to access environment variables
    $githubToken = $_ENV['GITHUB_TOKEN'] ?? getenv('GITHUB_TOKEN') ?? '';
    $githubSha = $_ENV['GITHUB_SHA'] ?? getenv('GITHUB_SHA') ?? '';
    $githubRepo = $_ENV['GITHUB_REPO'] ?? getenv('GITHUB_REPO') ?? 'iDigBio/Biospex';

    // Debug: Show available environment variables for troubleshooting
    writeln('Debug: Checking environment variables...');
    writeln('GITHUB_TOKEN present: '.(! empty($githubToken) ? 'YES' : 'NO'));
    writeln('GITHUB_SHA present: '.(! empty($githubSha) ? 'YES' : 'NO'));
    writeln('GITHUB_REPO: '.$githubRepo);

    // Validate required environment variables
    if (empty($githubToken) || empty($githubSha)) {
        // Provide more helpful error message with debugging information
        $envVars = array_keys($_ENV);
        $relevantEnvVars = array_filter($envVars, function ($key) {
            return strpos(strtoupper($key), 'GITHUB') !== false;
        });

        $errorMsg = "GITHUB_TOKEN and GITHUB_SHA environment variables are required.\n";
        $errorMsg .= 'Available GitHub-related env vars: '.implode(', ', $relevantEnvVars)."\n";
        $errorMsg .= 'All env vars count: '.count($envVars);

        throw new \Exception($errorMsg);
    }

    // Artifact naming convention: biospex-{git-sha}
    $artifactName = "biospex-{$githubSha}";
    writeln("Downloading CI artifact: {$artifactName}");

    // Step 1: Get artifact download URL from GitHub API
    $apiUrl = "https://api.github.com/repos/{$githubRepo}/actions/artifacts";
    $response = runLocally("curl -H 'Authorization: Bearer {$githubToken}' -H 'Accept: application/vnd.github.v3+json' '{$apiUrl}?name={$artifactName}&per_page=1'");
    $artifacts = json_decode($response, true);

    // Validate artifact exists
    if (empty($artifacts['artifacts'])) {
        throw new \Exception("No CI artifact found with name: {$artifactName}");
    }

    $downloadUrl = $artifacts['artifacts'][0]['archive_download_url'];
    cd('{{release_or_current_path}}');

    // Step 2: Download, extract, and deploy CI-built assets
    run("curl -L -H 'Authorization: Bearer {$githubToken}' -H 'Accept: application/vnd.github.v3+json' '{$downloadUrl}' -o artifact.zip");
    run('unzip -o -q artifact.zip');       // Extract artifact quietly, overwrite existing files

    // Debug: Check what was actually extracted
    writeln('Debug: Contents after extraction:');
    run('ls -la');

    // Find the deepest 'deployment-package' and rsync its contents to flatten
    $nestLevelCmd = run('find . -type d -name "deployment-package" -printf "%p\\n" | wc -l');
    $nests = (int) trim($nestLevelCmd);
    writeln("Detected {$nests} 'deployment-package' dirs post-unzip");

    if ($nests === 0) {
        writeln('Debug: No deployment-package found—assuming flat extraction');
    } elseif ($nests === 1) {
        // Single level: rsync as before
        run('rsync -av deployment-package/ ./');
        run('rm -rf deployment-package');
    } else {
        // Multiple nests: Find innermost and rsync up
        writeln('⚠️ Detected nesting; flattening...');
        $innermost = run('find . -type d -name "deployment-package" | sort -r | head -1');  // Deepest path
        $innermost = trim($innermost);
        if (! empty($innermost)) {
            run("rsync -av '{$innermost}/' ./");  // Copy contents of deepest to root
            // Clean all deployment-package dirs
            run('find . -type d -name "deployment-package" -exec rm -rf {} +');
        }
    }

    run('rm -f artifact.zip'); // Cleanup artifact file

    writeln('✅ CI artifacts deployed successfully - No server-side building required!');
});

/*
 * =============================================================================
 * OPCACHE MANAGEMENT
 * =============================================================================
 */

desc('Reset OpCache after deployment');
task('opcache:reset', function () {
    // Method 1: Direct PHP CLI OpCache reset (try first)
    try {
        run('php {{release_or_current_path}}/artisan tinker --execute="if (function_exists(\'opcache_reset\')) { opcache_reset(); echo \'OpCache reset via CLI\'; } else { echo \'OpCache not available via CLI\'; }"');
        writeln('✅ OpCache reset successful via CLI');
    } catch (Exception $e) {
        writeln('⚠️  CLI OpCache reset failed, trying webhook method...');

        // Method 2: Webhook-based OpCache reset (fallback)
        try {
            $webhookToken = $_ENV['OPCACHE_WEBHOOK_TOKEN'] ?? getenv('OPCACHE_WEBHOOK_TOKEN') ?? '';
            if (empty($webhookToken)) {
                throw new Exception('OPCACHE_WEBHOOK_TOKEN not set');
            }

            $hostname = currentHost()->get('hostname');
            $currentPath = run('readlink {{deploy_path}}/current');
            $appUrl = strpos($currentPath, 'dev.biospex') !== false
                ? 'https://dev.biospex.org'
                : 'https://biospex.org';

            $webhookUrl = "{$appUrl}/admin/opcache/reset/{$webhookToken}";
            $response = run("curl -X POST -H 'Content-Type: application/json' '{$webhookUrl}'");

            writeln('✅ OpCache reset successful via webhook');
            writeln('Response: '.$response);
        } catch (Exception $webhookException) {
            writeln('❌ Both CLI and webhook OpCache reset methods failed');
            writeln('CLI Error: '.$e->getMessage());
            writeln('Webhook Error: '.$webhookException->getMessage());

            // Don't fail the deployment, just warn
            writeln('⚠️  Deployment will continue without OpCache reset');
        }
    }
});

desc('Reset OpCache via webhook (reliable method)');
task('opcache:reset-webhook', function () {
    $webhookToken = $_ENV['OPCACHE_WEBHOOK_TOKEN'] ?? getenv('OPCACHE_WEBHOOK_TOKEN') ?? '';
    if (empty($webhookToken)) {
        throw new Exception('OPCACHE_WEBHOOK_TOKEN environment variable is required');
    }

    $hostname = currentHost()->get('hostname');
    $currentPath = run('readlink {{deploy_path}}/current');
    $appUrl = strpos($currentPath, 'dev.biospex') !== false
        ? 'https://dev.biospex.org'
        : 'https://biospex.org';

    $webhookUrl = "{$appUrl}/admin/opcache/reset/{$webhookToken}";
    $response = run("curl -X POST -H 'Content-Type: application/json' '{$webhookUrl}' -w '%{http_code}'");

    if (strpos($response, '200') === false) {
        throw new Exception('OpCache webhook reset failed. Response: '.$response);
    }

    writeln('✅ OpCache reset successful via webhook');
});

desc('Reset OpCache after deployment (Production Only)');
task('opcache:reset-production', function () {
    // Only execute on production host
    $currentHost = currentHost()->get('alias');
    if ($currentHost !== 'production') {
        writeln('⏭️  Skipping OpCache reset (not production environment)');

        return;
    }

    writeln('🔄 Resetting OpCache for production deployment...');
    invoke('opcache:reset');
});

/*
 * =============================================================================
 * DEPLOYMENT VERIFICATION
 * =============================================================================
 */

desc('Verify flat deployment structure');
task('deploy:verify-structure', function () {
    $nestCheck = run('find {{release_path}} -type d -name "deployment-package" | wc -l');
    if ((int) trim($nestCheck) > 0) {
        throw new \Exception("Nesting detected post-deploy: {$nestCheck} dirs. Check CI artifact.");
    }
    writeln('✅ Deployment structure verified: flat and clean');
});
