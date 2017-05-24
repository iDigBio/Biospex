<?php

namespace App\Services\Image;


class GdService extends ImageServiceBase
{
    /**
     * @param $file
     * @param $image
     * @return bool|int
     */
    public function writeImageToFile($file, $image)
    {
        return file_put_contents($file, $image);
    }
}