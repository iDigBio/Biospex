<?php

namespace App\Services\Actor;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;
use App\Services\Image\ImagickService;
use App\Services\Image\GdService;
use App\Services\Requests\HttpRequest;
use Illuminate\Contracts\Events\Dispatcher as Event;

class ActorImageService extends ActorServiceBase
{

    /**
     * @var ImagickService
     */
    public $imagickService;

    /**
     * @var
     */
    public $gdService;

    /**
     * @var Event
     */
    public $dispatcher;

    /**
     * @var HttpRequest
     */
    public $httpRequest;

    /**
     * @var
     */
    private $actor;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $subjects;

    /**
     * @var
     */
    private $missingImages = [];

    /**
     * ActorImageService constructor.
     *
     * @param ImagickService $imagickService
     * @param GdService $gdService
     * @param HttpRequest $httpRequest
     * @param Event $dispatcher
     * @internal param ImageService $imageService
     */
    public function __construct(
        ImagickService $imagickService,
        GdService $gdService,
        HttpRequest $httpRequest,
        Event $dispatcher
    )
    {
        $this->imagickService = $imagickService;
        $this->gdService = $gdService;
        $this->httpRequest = $httpRequest;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Set Actor
     * @param $actor
     */
    public function setActor($actor)
    {
        $this->actor = $actor;
    }

    /**
     * Process expedition for export.
     *
     * @param $subjects
     */
    public function getImages($subjects)
    {
        echo 'entered get images method ' . PHP_EOL;
        $this->subjects = $subjects;

        $this->httpRequest->setHttpProvider();

        $requests = function ()
        {
            foreach ($this->subjects as $index => $subject)
            {
                if ($this->checkPropertiesExist($subject))
                {
                    $this->fireActorUpdate();
                    continue;
                }

                yield $index => new Request('GET', str_replace(' ', '%20', $subject->accessURI));
            }
        };

        $pool = new Pool($this->httpRequest->getHttpClient(), $requests(), [
            'concurrency' => 5,
            'fulfilled'   => function ($response, $index)
            {
                echo 'fulfilled image ' . $index . PHP_EOL;
                $this->saveImage($response, $index);
            },
            'rejected'    => function ($reason, $index)
            {
                echo 'rejected image ' . $index . PHP_EOL;
                $this->fireActorUpdate();
                $this->setMissingImages($this->subjects[$index]->_id, $this->subjects[$index]->accessURI, 'Could not retrieve image from uri.');
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    /**
     * Save image to image path.
     *
     * @param $response
     * @param $index
     */
    private function saveImage($response, $index)
    {
        $image = $response->getBody()->getContents();

        if ($image === '' || $response->getStatusCode() !== 200)
        {
            echo 'missing image response ' . $index . PHP_EOL;
            $this->setMissingImages($this->subjects[$index]->_id, $this->subjects[$index]->accessURI, 'Image empty: ' . $response->getStatusCode());

            return;
        }

        echo 'set image source ' . $index . PHP_EOL;
        if ( ! $this->gdService->setImageFromSource($image, false))
        {
            echo 'could not set image information ' . $index . PHP_EOL;
            $this->setMissingImages($this->subjects[$index]->_id, $this->subjects[$index]->accessURI, 'Image corrupt: ' . $response->getStatusCode());

            return;
        }

        $ext = $this->gdService->getSourceExtension();
        echo 'set image ext ' . $ext . PHP_EOL;
        if ( ! $this->gdService->writeImageToFile($this->workingDirectory . '/' . $this->subjects[$index]->_id . $ext, $image))
        {
            echo 'failed to write image ' . $index . PHP_EOL;
            $this->setMissingImages($this->subjects[$index]->_id, $this->subjects[$index]->accessURI, 'Write image to file: ' . $response->getStatusCode());

            return;
        }

        echo 'image saved ' . $index . PHP_EOL;
        $this->fireActorUpdate();
    }

    public function convert($source, $filename)
    {
        $this->imagickService->createImagickObject();

        if ( ! $this->imagickService->readImageFromPath($source))
        {
            $this->setMissingImages($source, 'Could not create image from string: ' . $response->getStatusCode());

            return;
        }

        if ( ! $this->imagickService->generateAndSaveImage($this->subjects[$index]->_id, $destination))
        {
            $this->removeErrorFiles($index, $destination);
            $this->setMissingImages($this->subjects[$index], 'Could not save image to destination file');

            return;
        }

        $this->imageService->clearImagickObject();
    }


    /**
     * Fire actor update for processed count.
     */
    private function fireActorUpdate()
    {
        $this->actor->pivot->processed++;
        if ($this->actor->pivot->processed % 25 === 0 || ($this->subjects->count() - $this->actor->pivot->processed === 0) )
        {
            echo 'updating actor ' . $this->actor->pivot->processed . PHP_EOL;
            $this->dispatcher->fire('actor.pivot.processed', $this->actor);
        }
    }

    /**
     * Add missing image information to array.
     *
     * @param $subjectId
     * @param $accessURI
     * @param null $message
     */
    public function setMissingImages($subjectId, $accessURI, $message = null)
    {
        $this->missingImages[] = ['subjectId' => $subjectId, 'accessURI' => $accessURI, 'message' => $message];
    }

    /**
     * Return missing images array.
     *
     * @return mixed
     */
    public function getMissingImages()
    {
        return $this->missingImages;
    }

    /**
     * Check properties to see if image needs to be retrieved.
     *
     * @param $subject
     * @return bool
     */
    private function checkPropertiesExist($subject)
    {
        if (empty($subject->accessURI))
        {
            echo 'access uri empty ' . $subject->_id . PHP_EOL;
            $this->setMissingImages($subject->_id, $subject->accessURI, 'Image missing accessURI');

            return true;
        }

        if (count(glob($this->workingDirectory . '/' . $subject->_id . '.*')) > 0)
        {
            echo 'image exists ' . $subject->_id . PHP_EOL;
            return true;
        }

        return false;
    }

    /**
     * Write imagick to file.
     *
     * @param $file
     * @param $directory
     * @param $filename
     */
    public function writeImagickFile($file, $directory, $filename)
    {
        $this->imagickService->createImagickObject($file);

        $destination = $directory . '/' . $filename . '.jpg';
        if ( ! $this->imagickService->writeImagickImageToFile($destination))
        {
            $this->setMissingImages($filename, '', 'Could not write image to file.');
        }

        $this->imagickService->clearImagickObject();

        $this->fireActorUpdate();
    }
}