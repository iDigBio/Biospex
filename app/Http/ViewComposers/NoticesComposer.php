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

use App\Repositories\NoticeRepository;
use Illuminate\Contracts\View\View;

/**
 * Class NoticesComposer
 *
 * @package App\Http\ViewComposers
 */
class NoticesComposer
{

    /**
     * @var \App\Repositories\NoticeRepository
     */
    private $noticeRepo;

    /**
     * Create a new profile composer.
     *
     * @param \App\Repositories\NoticeRepository $noticeRepo
     */
    public function __construct(NoticeRepository $noticeRepo)
    {
        $this->noticeRepo = $noticeRepo;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $notices = $this->noticeRepo->getBy('enabled', 1);
        $notices = $notices->isEmpty() ? null : $notices;

        $view->with('notices', $notices);
    }
}