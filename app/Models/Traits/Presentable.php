<?php

namespace App\Models\Traits;

use App\Exceptions\PresenterException;

trait Presentable
{
    /**
     * @var \App\Presenters\Presenter
     */
    protected $presenterInstance;

    /**
     * @return mixed
     * @throws PresenterException
     */
    public function present()
    {
        if (is_object($this->presenterInstance)) {
            return $this->presenterInstance;
        }

        if (property_exists($this, 'presenter') and class_exists($this->presenter)) {
            $this->presenterInstance = new $this->presenter($this);
            return $this->presenterInstance = new $this->presenter($this);
        }

        throw new PresenterException('Property $presenter was not set correctly in '.get_class($this));
    }
}
