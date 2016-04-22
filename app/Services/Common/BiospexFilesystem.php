<?php

namespace App\Services\Common;

use Illuminate\Filesystem\Filesystem;

class BiospexFilesystem extends Filesystem
{
    public function directoryFilesByRegex($dir, $pattern)
    {
        $directory = new \DirectoryIterator($dir);
        $iterator = new \IteratorIterator($directory);

        return new \RegexIterator($iterator, $pattern);
    }

    public function directoryFiles($dir)
    {
        $directory = new \DirectoryIterator($dir);
        return new \IteratorIterator($directory);
    }
}