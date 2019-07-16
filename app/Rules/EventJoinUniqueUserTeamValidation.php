<?php

namespace App\Rules;

use App\Models\Event;
use Illuminate\Contracts\Validation\Rule;

class EventJoinUniqueUserTeamValidation implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $groupId = request()->get('team_id');
        $nfnUser = request()->get('nfn_user');

        $result = Event::whereHas('teams', function($query) use ($groupId){
            $query->where('id', $groupId);
        })->whereHas('teams.users', function($query) use($nfnUser){
            $query->where('nfn_user', $nfnUser);
        })->count();

        return ! $result;
    }

    /**
     * Get the validation error message.
     *
     */
    public function message()
    {
        return trans('messages.event_join_user_error');
    }
}
