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

namespace App\Services;

/**
 * Class RapidServiceBase
 *
 * @package App\Services
 */
class RapidServiceBase
{
    /**
     * Map header columns to tags.
     *
     * @param $headers
     * @param array $tags
     * @return \Illuminate\Support\Collection
     */
    public function mapColumns($headers, array $tags)
    {
        $mapped = collect($headers)->mapToGroups(function($header) use($tags){
            foreach ($tags as $tag) {
                if (preg_match('/'.$tag.'/', $header, $matches)) {
                    return [$matches[0] => $header];
                }
            }
            return ['unused' => $header];
        });

        return $mapped->forget('unused');
    }
}