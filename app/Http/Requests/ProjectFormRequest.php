<?php namespace Biospex\Http\Requests;

/**
 * ProjectFormRequest.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
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

use Cartalyst\Sentry\Sentry;

class ProjectFormRequest extends Request {

    public function rules()
    {
        return [
            'group_id'          => 'required|integer|min:1',
            'title'             => 'required|between:6,140',
            'contact'           => 'required',
            'contact_email'     => 'required|min:4|max:32|email',
            'description_short' => 'required|between:6,140',
            'description_long'  => 'required',
            'keywords'          => 'required',
            'banner'            => 'image|image_size:>=1200,>=300',
            'logo'              => 'image|image_size:<=300,<=200'
        ];

        /*
        switch($this->method())
        {
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'user.name.first' => 'required',
                    'user.name.last'  => 'required',
                    'user.email'      => 'required|email|unique:users,email',
                    'user.password'   => 'required|confirmed',
                ];
            }
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'user.name.first' => 'required',
                    'user.name.last'  => 'required',
                    'user.email'      => 'required|email|unique:users,email,'.$user->id,
                    'user.password'   => 'required|confirmed',
                ];
            }
            default:break;
        }
        */
    }


    public function authorize()
    {
        return Sentry::check();
    }

    // OPTIONAL OVERRIDE
    public function forbiddenResponse()
    {
        // Optionally, send a custom response on authorize failure
        // (default is to just redirect to initial page with errors)
        //
        // Can return a response, a view, a redirect, or whatever else
        return Response::make('Permission denied foo!', 403);
    }

    // OPTIONAL OVERRIDE
    public function response()
    {
        // If you want to customize what happens on a failed validation,
        // override this method.
        // See what it does natively here:
        // https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Http/FormRequest.php
    }
}