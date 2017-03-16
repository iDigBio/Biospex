<?php

namespace App\Exceptions;

use RuntimeException;

class EntityNotFoundException extends RuntimeException
{
    /**
     * Id of the affected model.
     *
     * @var string
     */
    protected $id;

    /**
     * Name of the affected model.
     *
     * @var string
     */
    protected $model;

    /**
     * Set the affected model.
     *
     * @param string $model
     * @param int    $id
     *
     * @return void
     */
    public function __construct($model, $id)
    {
        $this->id = $id;
        $this->model = $model;
        $this->message = "No results for model [{$model}] #{$id}.";
    }

    /**
     * Get the affected model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the affected model Id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
