<?php

namespace App\Services\File;

use App\Exceptions\CreateDirectoryException;
use App\Exceptions\FileUnzipException;
use Illuminate\Filesystem\Filesystem;
use Exception;

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
     * @throws CreateDirectoryException
     */
    public function makeDirectory($dir)
    {
        if ( ! $this->filesystem->isDirectory($dir) && ! $this->filesystem->makeDirectory($dir, 0775, true))
        {
            throw new CreateDirectoryException(trans('errors.create_dir', ['directory' => $dir]));
        }

        if ( ! $this->filesystem->isWritable($dir) && ! chmod($dir, 0775))
        {
            throw new CreateDirectoryException(trans('errors.write_dir', ['directory' => $dir]));
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
     * @throws FileUnzipException
     */
    public function unzip($zipFile, $dir)
    {
        try{
            shell_exec("unzip $zipFile -d $dir");
        }
        catch(Exception $e)
        {
            throw new FileUnzipException($e->getMessage());
        }
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