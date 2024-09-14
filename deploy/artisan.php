<?php

namespace Deployer;

task('artisan:update:queries', function () {
    cd('{{release_or_current_path}}');
    run('php artisan update:queries');
})->desc('Deploying files...');

task('artisan:deploy:files', function () {
    cd('{{release_or_current_path}}');
    run('php artisan deploy:files');
})->desc('Running update queries...');
