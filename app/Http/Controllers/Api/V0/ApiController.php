<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Api\V0;

use App\Serializers\DataArraySerializer;
use Illuminate\Routing\Controller as BaseController;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Response;

/**
 * Class ApiController
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
        $this->fractal = new Manager;
        $this->fractal->setSerializer(new DataArraySerializer);
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
     * @param  int  $statusCode  Value to set
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Send response created.
     *
     * @return \Illuminate\Http\Response
     */
    protected function respondWithCreated()
    {
        return response()->noContent(201);
    }

    /**
     * Respond with single item.
     *
     * @param  null  $resourceKey
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
     * @param  null  $resourceKey
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
     * @param  null  $resourceKey
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
     * @return mixed
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        $response = \Response::make(json_encode($array), $this->statusCode, $headers);
        $response->header('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Paginate results.
     */
    protected function paginate($current, $previous, $next, $count)
    {
        $this->cursor = new Cursor($current, $previous, $next, $count);
    }

    /**
     * Respond with error.
     *
     * @return mixed
     */
    protected function respondWithError($message, $errorCode)
    {
        if ($this->statusCode === 200) {
            trigger_error(
                'You better have a really good reason for erroring on a 200...',
                E_USER_WARNING
            );
        }

        return $this->respondWithArray([
            'error' => [
                'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param  string  $message
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
     * @param  string  $message
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
     * @param  string  $message
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
     * @param  string  $message
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
     * @param  string  $message
     * @return Response
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)
            ->respondWithError($message, self::CODE_WRONG_ARGS);
    }
}
