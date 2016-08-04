<?php
namespace App\Services\Common;

use Illuminate\Filesystem\Filesystem;

class FileSystemService
{

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * FileSystemService constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Check if path is directory.
     *
     * @param $dir
     * @return bool
     */
    public function isDir($dir)
    {
        return $this->filesystem->isDirectory($dir);
    }

    /**
     * Create directory.
     *
     * @param $dir
     * @return mixed
     * @throws \Exception
     */
    public function createDir($dir)
    {
        if (! $this->filesystem->isDirectory($dir)) {
            if (! $this->filesystem->makeDirectory($dir, 0775, true)) {
                throw new \Exception(trans('emails.error_create_dir', ['directory' => $dir]));
            }
        }

        return $dir;
    }

    /**
     * Make sure directory is writable.
     *
     * @param $dir
     * @throws \Exception
     */
    public function writeDir($dir)
    {
        if (! $this->filesystem->isWritable($dir)) {
            if (! chmod($dir, 0775)) {
                throw new \Exception(trans('emails.error_write_dir', ['directory' => $dir]));
            }
        }
    }

    /**
     * Save a file to destination path.
     *
     * @param $path
     * @param $contents
     * @throws \Exception
     */
    public function saveFile($path, $contents)
    {
        if ( ! $this->filesystem->put($path, $contents)) {
            throw new \Exception(trans('emails.error_save_file', ['directory' => $path]));
        }
    }

    /**
     * Move a file.
     *
     * @param $path
     * @param $target
     * @throws \Exception
     */
    public function moveFile($path, $target)
    {
        if (! $this->filesystem->move($path, $target)) {
            throw new \Exception(trans('emails.error_save_file', ['directory' => $path]));
        }
    }

    /**
     * Delete file.
     *
     * @param $file
     * @return bool
     */
    public function deleteFile($file)
    {
        return $this->filesystem->delete($file);
    }

    /**
     * @return string
     */
    public function get($path)
    {
        return $this->filesystem->get($path);
    }

    /**
     * @param $file
     * @return bool
     */
    public function isFile($file)
    {
        return $this->filesystem->isFile($file);
    }
}