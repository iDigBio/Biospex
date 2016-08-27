<?php

namespace App\Services\Actor;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;
use RuntimeException;

class ActorFileService
{
    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var Config
     */
    public $config;

    /**
     * ActorFileService constructor.
     * @param Filesystem $filesystem
     * @param Config $config
     */
    public function __construct(
        Filesystem $filesystem,
        Config $config
    )
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * @param $dir
     * @throws RuntimeException
     */
    public function makeDirectory($dir)
    {
        if ( ! $this->filesystem->isDirectory($dir) && ! $this->filesystem->makeDirectory($dir, 0775, true))
        {
            throw new RuntimeException(trans('emails.error_create_dir', ['directory' => $dir]));
        }

        if ( ! $this->filesystem->isWritable($dir) && ! chmod($dir, 0775))
        {
            throw new RuntimeException(trans('emails.error_write_dir', ['directory' => $dir]));
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
     * Get files from working directory.
     *
     * @param $dir
     * @return array
     */
    public function getFiles($dir)
    {
        return $this->filesystem->files($dir);
    }

    /**
     * Return true if files in directory.
     *
     * @param $dir
     * @return bool
     */
    public function checkFileCount($dir)
    {
        return count($this->getFiles($dir)) !== 0;
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
}