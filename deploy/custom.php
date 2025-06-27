<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Deployer;

/**
 * Deploys application-specific files using a custom artisan command.
 * Changes to the release directory and executes the app:deploy-files command
 * to handle any specific file deployment requirements.
 */
/**
 * Execute database update queries for the application
 * Changes to the release or current path and runs the update-queries artisan command
 */
desc('Running update queries...');
task('artisan:app:update-queries', function () {
    cd('{{release_or_current_path}}');
    run('php artisan app:update-queries');
});

desc('Deploying files...');
task('artisan:app:deploy-files', function () {
    cd('{{release_or_current_path}}');
    run('php artisan app:deploy-files');
});

/**
 * Sets appropriate ownership and permissions for deployment.
 * Updates file ownership to ubuntu:www-data and clears log files
 * to ensure proper application functioning and security.
 */
desc('Setting permissions...');
task('set:permissions', function () {
    run('sudo chown -R ubuntu.www-data {{deploy_path}}');
    run('sudo truncate -s 0 {{release_or_current_path}}/storage/logs/*.log');
});

/**
 * Install Yarn dependencies
 * Runs yarn install in the release path, ignoring engine requirements
 */
desc('Install project dependencies');
task('yarn:run-install', function () {
    cd('{{release_or_current_path}}');
    run('yarn install --ignore-engines');
});

/**
 * Build production assets using NPM
 * Executes npm run prod in the release path
 */
desc('Build project');
task('npm:run-build', function () {
    cd('{{release_path}}');
    run('npm run prod');
});

/**
 * Uploads the appropriate environment configuration file.
 * Copies the AWS production environment file to the shared deployment directory
 * where it will be symlinked as .env for the application.
 */
desc('Upload env file depending on the host');
task('upload:env', function () {
    upload('.env.aws.prod', '{{deploy_path}}/shared/.env');
});

/**
 * Reload Supervisor configuration
 * Executes reread and update commands for Supervisor
 */
desc('Supervisor reread and update');
task('supervisor:reread-update', function () {
    cd('{{release_path}}');
    run('sudo supervisorctl reread');
    run('sudo supervisorctl update');
});