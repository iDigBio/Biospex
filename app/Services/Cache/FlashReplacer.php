<?php

/*
 * Copyright (C) 2014 - 2026, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Cache;

use Spatie\ResponseCache\Replacers\Replacer;
use Symfony\Component\HttpFoundation\Response;

class FlashReplacer implements Replacer
{
    public function prepareResponseToCache(Response $response): void
    {
        // No-op: We don't want to store anything special in the cached HTML
    }

    public function replaceInCachedResponse(Response $response): void
    {
        $status = ['success', 'info', 'warning', 'danger'];

        foreach ($status as $type) {
            if (session()->has($type)) {
                $payload = json_encode([
                    'type' => $type,
                    'message' => (string) session($type),
                    'icon' => match ($type) {
                        'success' => 'check-circle',
                        'info' => 'info-circle',
                        'warning' => 'exclamation-circle',
                        'danger' => 'times-circle',
                        default => 'info-circle',
                    },
                ]);

                // Ensure the cookie is set correctly
                $response->headers->setCookie(cookie('app_flash', $payload, 0, '/', null, false, false, false, 'Lax'));

                // Do not cache the response that contains the cookie header
                $response->headers->set('laravel-responsecache', 'do-not-cache');

                session()->forget($type);
                break;
            }
        }
    }
}
