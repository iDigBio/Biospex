<?php namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
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
     */
    public function __construct(Thumbnail $thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Build html used by jQuery qTip
     *
     * @return string
     */
    public function html()
    {
        $url = \Input::get('url');
        return '<div><img src="/images/preview?url='.urlencode($url).'" /></div>';
    }

    /**
     * Return resized image for jQuery qTip
     *
     * @return \Illuminate\Http\Response
     */
    public function preview()
    {
        $url = \Input::get('url');
        $thumb = $this->thumbnail->getThumbNail(urldecode($url));

        $response = \Response::make($thumb, 200);
        $response->header('content-type', $this->thumbnail->getMimeType());

        // We return our image here.
        return $response;
    }
}
