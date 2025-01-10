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
use App\Jobs\ExpertReconcileReviewPublishJob;
use App\Models\Expedition;
use Redirect;

class ExpertReconcilePublishController extends Controller
{
    /**
     * Publish reconciled csv file.
     */
    public function __invoke(Expedition $expedition)
    {
        ExpertReconcileReviewPublishJob::dispatch($expedition);

        return Redirect::route('admin.expeditions.show', [$expedition])
            ->with('success', t('The Expert Review Publish job has been submitted. You will receive an email when it has completed.'));
    }
}
