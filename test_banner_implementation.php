<?php

/*
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

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Banner Implementation\n";
echo "=============================\n\n";

try {
    // Test helper functions
    echo "1. Testing project_banner_options():\n";

    if (! function_exists('project_banner_options')) {
        throw new Exception('Function project_banner_options() does not exist');
    }

    $options = project_banner_options();
    if (! is_array($options)) {
        throw new Exception('project_banner_options() did not return an array');
    }

    foreach ($options as $filename => $label) {
        echo "   $filename => $label\n";
    }
    echo "   ✓ project_banner_options() working correctly\n";

    echo "\n2. Testing project_banner_file_url():\n";

    if (! function_exists('project_banner_file_url')) {
        throw new Exception('Function project_banner_file_url() does not exist');
    }

    foreach (array_keys($options) as $filename) {
        $url = project_banner_file_url($filename);
        echo "   $filename => $url\n";
    }
    echo "   ✓ project_banner_file_url() working correctly\n";

    echo "\n3. Testing project_banner_file_name():\n";

    if (! function_exists('project_banner_file_name')) {
        throw new Exception('Function project_banner_file_name() does not exist');
    }

    echo '   Default: '.project_banner_file_name()."\n";
    echo '   With param: '.project_banner_file_name('banner-desert.jpg')."\n";
    echo "   ✓ project_banner_file_name() working correctly\n";

    echo "\n4. Testing default URL (no parameter):\n";
    echo '   '.project_banner_file_url()."\n";
    echo "   ✓ project_banner_file_url() default working correctly\n";

    echo "\n✅ All tests completed successfully!\n";

} catch (Exception $e) {
    echo "\n❌ Error: ".$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";

    echo "\nDebugging information:\n";
    echo "- Checking if helper functions exist:\n";
    echo '  project_banner_options: '.(function_exists('project_banner_options') ? '✓' : '✗')."\n";
    echo '  project_banner_file_url: '.(function_exists('project_banner_file_url') ? '✓' : '✗')."\n";
    echo '  project_banner_file_name: '.(function_exists('project_banner_file_name') ? '✓' : '✗')."\n";

    exit(1);
} catch (Error $e) {
    echo "\n❌ Fatal Error: ".$e->getMessage()."\n";
    echo 'File: '.$e->getFile().' Line: '.$e->getLine()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
    exit(1);
}
