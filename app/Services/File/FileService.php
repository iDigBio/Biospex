<?php
/**
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

namespace App\Services\File;

use Exception;
use Illuminate\Filesystem\Filesystem;

class FileService
{
    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * FileService constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param $dir
     * @throws \Exception
     */
    public function makeDirectory($dir)
    {
        if ( ! $this->filesystem->isDirectory($dir) && ! $this->filesystem->makeDirectory($dir, 0775, true))
        {
            throw new Exception(t('Unable to create directory: :directory', [':directory' => $dir]));
        }

        if ( ! $this->filesystem->isWritable($dir) && ! chmod($dir, 0775))
        {
            throw new Exception(t('Unable to make directory writable: %s', $dir));
        }
    }


    /**
     * Compress directories.
     *
     * @param $workingDir
     * @param $storagePath
     * @return array
     */
    public function compressDirectories($workingDir, $storagePath)
    {
        $directories = $this->filesystem->directories($workingDir);

        $compressed = [];
        foreach ($directories as $directory)
        {
            $baseName = basename($directory);
            $compressed[] = $tarFile = $baseName . '.tar.gz';
            exec("tar -zcf $storagePath/$tarFile -C $workingDir $baseName");
        }

        return $compressed;
    }

    /**
     * Check if file exists.
     *
     * @param $file
     * @return bool
     */
    public function checkFileExists($file)
    {
        return count($this->filesystem->glob($file)) > 0;
    }

    /**
     * Return true if files in directory.
     *
     * @param $dir
     * @return bool
     */
    public function checkFileCount($dir)
    {
        return count($this->filesystem->files($dir)) !== 0;
    }

    /**
     * Delete directories.
     *
     * @param array $directories
     */
    public function delete(array $directories)
    {
        foreach ($directories as $directory)
        {
            $this->filesystem->deleteDirectory($directory);
        }
    }

    /**
     * Unzip file in directory.
     *
     * @param $zipFile
     * @param $dir
     */
    public function unzip($zipFile, $dir)
    {
        shell_exec("unzip $zipFile -d $dir");
    }

    /**
     * @param $file
     * @param $image
     * @return bool|int
     */
    public function writeToFile($file, $image)
    {
        return $this->filesystem->put($file, $image);
    }
}