<?php

/*
 * Copyright (c) 2022. Digitization Academy
 * idigacademy@gmail.com
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

desc('Setting permissions...');
task('set:permissions', function () {
    run('sudo chown -R ubuntu.www-data {{deploy_path}}');
    run('sudo truncate -s 0 {{release_or_current_path}}/storage/logs/*.log');
});

desc('Install project dependencies');
task('yarn:run-install', function () {
    cd('{{release_or_current_path}}');
    run('yarn install --frozen-lockfile --ignore-engines');
});

desc('Build project dependencies');
task('npm:run-build', function () {
    cd('{{release_or_current_path}}');
    run('npm run production');
});

desc('Upload env file depending on the host');
task('upload:env', function () {
    $alias = currentHost()->get('alias');
    $file = match ($alias) {
        'production' => '/mnt/c/Users/bruhn/Cloud-Drive/Local-Dev-Install/env/.env.aws.prod',
        'development' => '/mnt/c/Users/bruhn/Cloud-Drive/Local-Dev-Install/env/.env.aws.dev'
    };
    upload($file, '{{deploy_path}}/shared/.env');
});

desc('Reload Supervisor');
task('supervisor:reload', function () {
    run('sudo supervisorctl reread');
    run('sudo supervisorctl update');
    run('sudo systemctl restart supervisor');
});

desc('Reload Supervisor Restart Group');
task('supervisor:restart-group', function () {
    $alias = currentHost()->get('alias');
    run('sudo supervisorctl restart '.$alias.':');
});
