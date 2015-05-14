<?php
/**
 * validation.php
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
return array(

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted"         => "El :attribute debe ser aceptada.",
    "active_url"       => "El :attribute no es una URL válida.",
    "after"            => "El :attribute debe ser una fecha después :date.",
    "alpha"            => "El :attribute sólo puede contener letras.",
    "alpha_dash"       => "El :attribute sólo puede contener letras, números y guiones.",
    "alpha_num"        => "El :attribute sólo puede contener letras y números.",
    "array"            => "El :attribute debe ser una matriz.",
    "before"           => "El :attribute debe ser una fecha antes :date.",
    "between"          => array(
        "numeric" => "El :attribute debe estar entre :min y :max.",
        "file"    => "El :attribute debe estar entre :min y :max kilobytes.",
        "string"  => "El :attribute debe estar entre :min y :max characters.",
        "array"   => "El :attribute debe estar entre :min y :max items.",
    ),
    "confirmed"        => "El :attribute confirmación no coincide.",
    "date"             => "El :attribute no es una fecha válida.",
    "date_format"      => "El :attribute no coincide con el formato :format.",
    "different"        => "El :attribute y :other must be different.",
    "digits"           => "El :attribute debe ser :digits dígitos.",
    "digits_between"   => "El :attribute debe estar entre :min y :max dígitos.",
    "email"            => "El Formato de :attribute no es válido.",
    "exists"           => "El seleccionado :attribute is invalid.",
    "image"            => "El :attribute debe ser una imagen.",
    "in"               => "El seleccionado :attribute is invalid.",
    "integer"          => "El :attribute debe ser un número entero.",
    "ip"               => "El :attribute debe ser una dirección IP válida.",
    "max"              => array(
        "numeric" => "El :attribute no puede ser mayor que :max.",
        "file"    => "El :attribute no puede ser mayor que :max kilobytes.",
        "string"  => "El :attribute no puede ser mayor que :max characters.",
        //"array"   => "El :attribute no puede tener más de :max items.",
    ),
    "mimes"            => "El :attribute debe ser un archivo de type: :values.",
    "min"              => array(
        "numeric" => "El :attribute debe ser al menos :min.",
        "file"    => "El :attribute debe ser al menos :min kilobytes.",
        "string"  => "El :attribute debe ser al menos :min characters.",
        //"array"   => "El :attribute debe tener al menos :min items.",
    ),
    "not_in"           => "El seleccionado :attribute no es válido.",
    "numeric"          => "El :attribute debe ser un número.",
    "regex"            => "El :attribute formato no es válido.",
    "required"         => "El :attribute se requiere campo.",
    "required_if"      => "El :attribute se requiere campo cuando :other is :value.",
    "required_with"    => "El :attribute se requiere campo cuando :values is present.",
    "required_without" => "El :attribute se requiere campo cuando :values is not present.",
    "same"             => "El :attribute y :other debe coincidir.",
    "size"             => array(
        "numeric" => "El :attribute debe ser :size.",
        "file"    => "El :attribute debe ser :size kilobytes.",
        "string"  => "El :attribute debe ser :size carácters.",
        "array"   => "El :attribute must contain :size artículos.",
    ),
    "unique"           => "El :attribute que ya se ha tomado.",
    "url"              => "El :attribute formato no es válido.",


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => array(
        'oldPassword' => array(
            'required' => 'Debe introducir la contraseña antigua.',
            'min' => 'Su antigua contraseña debe tener como mínimo 6 caracteres.',
        ),
        'newPassword' => array(
            'required' => 'Debe introducir una nueva contraseña.',
            'min' => 'Su nueva contraseña debe tener como mínimo 6 caracteres.',
        ),
        'newPassword_confirmation' => array(
            'required' => 'Debe confirmar su nueva contraseña.',
        ),
        'minutes' => array(
            'numeric' => 'Minutos debe ser un número.',
            'required' => 'Se debe especificar la longitud de suspensión en cuestión de minutos.',
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => array(),
);