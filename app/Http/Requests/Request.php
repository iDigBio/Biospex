<?php

namespace App\Http\Requests;

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
        // Normally would use is_callable but it returns true. Need to investigate
        if (method_exists($this, 'inputChange')) {
            return $this->container->call([$this, 'inputChange']);
        }

        return $this->all();
    }
}
