<?php namespace App\Validation;

use Illuminate\Validation\Factory;

abstract class AbstractLaravelValidator    implements ValidableInterface
{
    /**
     * Validator
     *
     * @var \Illuminate\Validation\Factory 
     */
    protected $validator;

    /**
     * Validation data key => value array
     *
     * @var Array 
     */
    protected $data = array();

    /**
     * Validation errors
     *
     * @var Array
     */
    protected $errors = array();

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array();

    /**
     * Custom Validation Messages
     *
     * @var Array
     */
    protected $messages = array();

    public function __construct(Factory $validator)
    {
        $this->validator = $validator;

        // Retrieve Custom Validation Messages & Pass them to the validator.
        $this->messages = array_dot(trans('validation.custom'));
    }

    /**
     * Set data to validate
     *
     * @return \App\Validation\AbstractLaravelValidator
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Validation passes or fails
     *
     * @return boolean 
     */
    public function passes()
    {
        $validator = $this->validator->make($this->data, $this->rules, $this->messages);

        if ($validator->fails()) {
            $this->errors = $validator->messages();
            return false;
        }


        return true;
    }

    /**
     * Return errors, if any
     *
     * @return array 
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Modify Rules on the fly
     *
     * @param string $rule
     * @param string $column DB Column to check against
     * @param string $ignore
     *
     * @return \App\Validation\AbstractLaravelValidator
     */
    public function modifyRules($rule, $column, $ignore)
    {
        $this->rules[$rule] .= ',' . $column . ',' . $ignore;
        return $this;
    }
}
