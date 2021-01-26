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

namespace App\Services\Export;


use App\Services\CsvService;
use App\Services\MongoDbService;

/**
 * Class RapidExportFactoryType
 *
 * @package App\Services\Export
 */
class RapidExportFactoryType
{
    /**
     * Create class for export type.
     *
     * @param string $type
     * @return \App\Services\Export\CsvExportType|\Illuminate\Contracts\Foundation\Application|mixed
     * @throws \Exception
     */
    public static function create(string $type)
    {
        if ($type === 'csv') {
            return app(CsvExportType::class, [MongoDbService::class, CsvService::class]);
        }

        throw new \Exception(t('Cannot create Rapid Export Type class.'));
    }
}