<?php

namespace App\Http\Controllers\Api\V0;

use Illuminate\Routing\Controller as BaseController;
use App\Serializers\DataArraySerializer;
use Illuminate\Support\Facades\Response;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;

/**
 * Class ApiController
 *
 * @package Api\Http\Controllers\V1
 */
class ApiController extends BaseController
{
    protected $statusCode = 200;

    const CODE_WRONG_ARGS = 'GEN-WRONG-ARGUMENTS';
    const CODE_NOT_FOUND = 'GEN-NOT-FOUND';
    const CODE_INTERNAL_ERROR = 'GEN-INTERNAL_ERROR';
    const CODE_UNAUTHORIZED = 'GEN-UNAUTHORIZED';
    const CODE_FORBIDDEN = 'GEN-FORBIDDEN';
    const CODE_INVALID_MIME_TYPE = 'GEN-INVALID-MIME-TYPE';

    /**
     * @var Manager
     */
    protected $fractal;

    /**
     * @var Cursor
     */
    protected $cursor = null;

    /**
     * Set custom serializer to change data resource key.
     */
    public function setManager()
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new DataArraySerializer());
    }

    /**
     * Getter for statusCode
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Respond with single item.
     *
     * @param $item
     * @param $callback
     * @param null $resourceKey
     * @return mixed
     */
    protected function respondWithItem($item, $callback, $resourceKey = null)
    {
        $this->setManager();

        $resource = new Item($item, $callback, $resourceKey);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * Respond with PusherTranscription Collection.
     *
     * @param $collection
     * @param $callback
     * @param $totalCount
     * @param null $resourceKey
     * @return mixed
     */
    protected function respondWithPusherCollection($collection, $callback, $totalCount, $resourceKey = null)
    {
        $this->setManager();

        $resource = new Collection($collection, $callback, $resourceKey);

        $this->cursor === null ?: $resource->setCursor($this->cursor);

        $rootScope = $this->fractal->createData($resource)->toArray();

        $newScope['numFound'] = $totalCount;
        $newScope['start'] = $rootScope['meta']['cursor']['current'];
        $newScope['rows'] = $rootScope['meta']['cursor']['count'];

        $rootScope = $newScope + $rootScope;

        return $this->respondWithArray($rootScope);
    }

    /**
     * Respond with Collection.
     *
     * @param $collection
     * @param $callback
     * @param null $resourceKey
     * @return mixed
     */
    protected function respondWithCollection($collection, $callback, $resourceKey = null)
    {
        $this->setManager();

        $resource = new Collection($collection, $callback, $resourceKey);

        $this->cursor === null ?: $resource->setCursor($this->cursor);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * Respond with array.
     *
     * @param array $array
     * @param array $headers
     * @return mixed
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        $response = Response::make(json_encode($array), $this->statusCode, $headers);
        $response->header('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Paginate results.
     *
     * @param $current
     * @param $previous
     * @param $next
     * @param $count
     */
    protected function paginate($current, $previous, $next, $count)
    {
        $this->cursor = new Cursor($current, $previous, $next, $count);
    }

    /**
     * Respond with error.
     *
     * @param $message
     * @param $errorCode
     * @return mixed
     */
    protected function respondWithError($message, $errorCode)
    {
        if ($this->statusCode === 200) {
            trigger_error(
                "You better have a really good reason for erroring on a 200...",
                E_USER_WARNING
            );
        }

        return $this->respondWithArray([
            'error' => [
                'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message,
            ]
        ]);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)
            ->respondWithError($message, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)
            ->respondWithError($message, self::CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)
            ->respondWithError($message, self::CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)
            ->respondWithError($message, self::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)
            ->respondWithError($message, self::CODE_WRONG_ARGS);
    }
}