<?php namespace Biospex\Validation;
/**
 * AbstractLaravelValidator.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

use Illuminate\Validation\Factory;

abstract class AbstractLaravelValidator	implements ValidableInterface {

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
	 * @return \Biospex\Validation\AbstractLaravelValidator
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

		if ($validator->fails() )
		{
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
     * @return \Biospex\Validation\AbstractLaravelValidator
     */
    public function modifyRules($rule, $column, $ignore)
    {
        $this->rules[$rule] .= ',' . $column . ',' . $ignore;
        return $this;
    }
	
}