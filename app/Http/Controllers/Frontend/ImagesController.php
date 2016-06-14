<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Services\Image\Thumbnail;

class ImagesController extends Controller
{
    /**
     * @var Thumbnail
     */
    protected $thumbnail;

    /**
     * Construct
     *
     * @param Thumbnail $thumbnail
     * @param Request $request
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
     * @return \Illuminate\Http\Response
     */
    public function preview()
    {
        $url = Request::input('url');
        $thumb = $this->thumbnail->getThumbnail(urldecode($url));

        return '<img src="data:image/jpeg;base64,' . base64_encode($thumb) . '" />';
    }
}
