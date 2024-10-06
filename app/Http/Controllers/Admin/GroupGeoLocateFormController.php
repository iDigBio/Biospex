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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeoLocateForm;
use App\Models\Group;
use App\Services\Permission\CheckPermission;
use Illuminate\Support\Facades\Redirect;
use Throwable;

class GroupGeoLocateFormController extends Controller
{
    public function __invoke(Group $group, GeoLocateForm $form)
    {
        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::route('admin.groups.index')->with('danger', t('You do not have permission to delete this form.'));
        }

        try {
            $form->loadCount('expeditions');

            if ($form->expeditions_count > 0) {
                return Redirect::route('admin.groups.show', [$group])
                    ->with('danger', t('GeoLocateExport Form cannot be deleted while still being used by Expeditions.'));
            }

            $form->delete();

            return Redirect::route('admin.groups.show', [$group])->with('success', t('GeoLocateExport Form was deleted.'));
        } catch (Throwable $throwable) {
            return Redirect::route('admin.groups.show', [$group])->with('danger', t('There was an error deleting the GeoLocateExport Form.'.$t->getMessage()));
        }
    }
}
