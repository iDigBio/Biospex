<?php

namespace App\Services\Actor;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;
use RuntimeException;

class ActorFileService
{

    public $workingDir;
    public $workingDirOrig;
    public $workingDirConvert;

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
        $this->scratchDir = $config->get('config.scratch_dir');

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
     * Set working directories for actors.
     *
     * @param $name
     * @throws RuntimeException
     */
    public function setDirectories($name)
    {
        $this->workingDir = $this->scratchDir . '/' . $name;
        $this->makeDirectory($this->workingDir);
        $this->workingDirOrig = $this->workingDir . '/orig';
        $this->makeDirectory($this->workingDirOrig);
        $this->workingDirConvert = $this->workingDir . '/convert';
        $this->makeDirectory($this->workingDirConvert);
    }

    /**
     * Compress directories.
     *
     * @param array $directories
     * @return array
     */
    public function compressDirectories(array $directories)
    {
        $compressed = [];
        foreach ($directories as $directory)
        {
            $tarFile = $directory . '.tar';
            $a = new \PharData($tarFile);
            $a->buildFromDirectory($directory);
            $a->compress(\Phar::GZ);
            unset($a);
            $this->filesystem->delete($tarFile);
            $this->filesystem->deleteDirectory($directory);

            $compressed[] = $tarFile . '.gz';
        }

        return $compressed;
    }

    /**
     * Move tar files to export folder.
     *
     * @param $originalDir
     * @param $destinationDir
     */
    public function moveCompressedFiles($originalDir, $destinationDir)
    {
        $files = $this->filesystem->glob($originalDir . '/*.tar.gz');
        foreach ($files as $file)
        {
            $baseName = pathinfo($file, PATHINFO_BASENAME);
            $this->filesystem->move($file, $destinationDir . '/' . $baseName);
        }
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