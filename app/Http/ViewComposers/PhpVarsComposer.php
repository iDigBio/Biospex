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

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use JavaScript;
use Session;

/**
 * Class PhpVarsComposer
 */
class PhpVarsComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        JavaScript::put([
            'groupIds' => json_encode(Session::get('groupIds')),
            'ocrChannel' => config('config.poll_ocr_channel'),
            'exportChannel' => config('config.poll_export_channel'),
            'imagePreviewPath' => '/admin/images/preview?url=',
            'habitatBannersPath' => '/images/habitat-banners/',
        ]);

        $view->with(['common.process-modal', 'common.modal', 'common.project-modal']);
    }
}
