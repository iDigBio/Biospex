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

namespace App\Http\Controllers\Traits;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait HandlesSns
{
    /**
     * Get the SNS message as array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Aws\Sns\Message
     */
    public function getSnsMessage(Request $request): Message
    {
        try {
            return Message::fromJsonString(
                $request->getContent() ?: file_get_contents('php://input')
            );
        } catch (Exception $e) {
            return new Message([]);
        }
    }

    /**
     * Check if the SNS message is valid.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function snsMessageIsValid(Request $request): bool
    {
        try {
            return $this->getMessageValidator($request)->isValid(
                $this->getSnsMessage($request)
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the message validator instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Aws\Sns\MessageValidator
     */
    protected function getMessageValidator(Request $request): MessageValidator
    {
        if (App::environment(['testing', 'local'])) {
            return new MessageValidator(function ($url) use ($request) {
                if ($certificate = $request->sns_certificate) {
                    return $certificate;
                }

                if ($certificate = $request->header('X-Sns-Testing-Certificate')) {
                    return $certificate;
                }

                return $url;
            });
        }

        return new MessageValidator;
    }
}
