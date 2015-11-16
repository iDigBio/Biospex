<?php namespace Biospex\Form\Project;

/**
 * ProjectFormLaravelValidator.php
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

use Biospex\Validation\AbstractLaravelValidator;

class ProjectFormLaravelValidator extends AbstractLaravelValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = [
        'group_id'          => 'required|integer|min:1',
        'title'             => 'required|between:6,140|unique:projects',
        'contact'           => 'required',
        'contact_email'     => 'required|min:4|max:32|email',
        'description_short' => 'required|between:6,140',
        'description_long'  => 'required',
        'keywords'          => 'required',
        'workflow_id'       => 'required',
        'banner'            => 'image|image_size:>=1200,>=300',
        'logo'              => 'image|image_size:<=300,<=200'
    ];
}
