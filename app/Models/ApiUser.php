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

namespace App\Models;

use App\Notifications\ApiUserResetPasswordNotification;
use App\Notifications\VerifyApiEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class ApiUser extends Authenticatable implements MustVerifyEmail
{

    use HasApiTokens, Notifiable, LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'api_users';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    /**
     * @inheritDoc
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyApiEmail());
    }

    /**
     * Send password reset notification.
     *
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ApiUserResetPasswordNotification($token));
    }
}
