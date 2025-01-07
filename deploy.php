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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Deployer;

require 'recipe/laravel.php';
require 'deploy/custom.php';

// Config
set('repository', 'https://github.com/iDigBio/Biospex.git');
set('base_path', '/data/web');
set('remote_user', 'ubuntu');
set('php_fpm_version', '8.3');
set('ssh_multiplexing', true);
set('writable_mode', 'chmod');
set('keep_releases', 3);

// Hosts
/*host('production')
    ->setHostname('3.142.169.134')
    ->setDeployPath('{{base_path}}/biospex')
    ->set('branch', 'master');*/

host('development')
    ->setHostname('3.142.169.134')
    ->setDeployPath('{{base_path}}/dev.biospex')
    ->set('branch', 'development');

// Tasks
desc('Deploys your project');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'artisan:storage:link',
    'upload:env',
    'yarn:run-install',
    'artisan:nova:publish',
    'artisan:app:deploy-files',
    'artisan:cache:clear',
    'artisan:config:clear',
    'artisan:event:clear',
    'artisan:optimize:clear',
    'artisan:route:clear',
    'artisan:view:clear',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:event:cache',
    'artisan:optimize',
    'artisan:migrate',
    'artisan:app:update-queries',
    'set:permissions',
    'deploy:publish',
    'supervisor:reload',
    'artisan:queue:restart',
]);

// Hooks
after('deploy:failed', 'deploy:unlock');
