<?php

namespace Biospex\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * Validate the input.
     *
     * @param  \Illuminate\Validation\Factory  $factory
     * @return \Illuminate\Validation\Validator
     */
    public function validator($factory)
    {
        return $factory->make(
            $this->alterInput(), $this->container->call([$this, 'rules']), $this->messages()
        );
    }

    /**
     * Alter the form input.
     *
     * @return array
     */
    protected function alterInput()
    {
        if (is_callable([$this, 'inputChange'])) {
            return $this->container->call([$this, 'inputChange']);
        }

        return $this->all();
    }
}
