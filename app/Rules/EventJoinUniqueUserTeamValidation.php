<?php

/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Rules;

use App\Models\Event;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class EventJoinUniqueUserTeamValidation
 */
class EventJoinUniqueUserTeamValidation implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct() {}

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $groupId = \Request::get('team_id');
        $nfnUser = \Request::get('nfn_user');

        $result = Event::whereHas('teams', function ($query) use ($groupId) {
            $query->where('id', $groupId);
        })->whereHas('teams.users', function ($query) use ($nfnUser) {
            $query->where('nfn_user', $nfnUser);
        })->count();

        return ! $result;
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return t('User already assigned to Event team.');
    }
}
