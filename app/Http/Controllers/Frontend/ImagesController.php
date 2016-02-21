<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Image\Thumbnail;

class ImagesController extends Controller
{
    /**
     * @var Thumbnail
     */
    protected $thumbnail;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Construct
     *
     * @param Thumbnail $thumbnail
     * @param Request $request
     */
    public function __construct(
        Thumbnail $thumbnail,
        Request $request
    )
    {
        $this->thumbnail = $thumbnail;
        $this->request = $request;
    }

    /**
     * Return resized image
     *
     * @return \Illuminate\Http\Response
     */
    public function preview()
    {
        $url = $this->request->input('url');
        $thumb = $this->thumbnail->getThumbNail(urldecode($url));

        return '<img src="data:image/jpeg;base64,' . base64_encode($thumb) . '" />';
    }
}
