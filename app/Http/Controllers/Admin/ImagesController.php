<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Image\Thumbnail;

class ImagesController extends Controller
{
    /**
     * @var Thumbnail
     */
    public $thumbnail;

    /**
     * Construct
     *
     * @param Thumbnail $thumbnail
     */
    public function __construct(
        Thumbnail $thumbnail
    )
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Return resized image
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function preview()
    {
        $url = request()->input('url');
        $thumb = $this->thumbnail->getThumbnail(urldecode($url));

        return '<img src="data:image/jpeg;base64,' . base64_encode($thumb) . '" />';
    }
}
