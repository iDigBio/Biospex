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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BingoFormRequest;
use App\Models\Bingo;
use App\Services\Bingo\BingoService;
use App\Services\Permission\CheckPermission;
use App\Services\Project\ProjectService;
use Auth;
use Redirect;
use View;

/**
 * Class BingoController
 */
class BingoController extends Controller
{
    /**
     * BingoController constructor.
     */
    public function __construct(
        protected BingoService $bingoService,
        protected ProjectService $projectService
    ) {}

    /**
     * Display admin index for bingo games created by user.
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $bingos = $this->bingoService->getAdminIndex(Auth::user());

        return View::make('admin.bingo.index', compact('bingos'));
    }

    /**
     * Create bingo.
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        $projects = $this->projectService->getProjectEventSelect();

        return View::make('admin.bingo.create', compact('projects'));
    }

    /**
     * Store bingo.
     */
    public function store(BingoFormRequest $request): \Illuminate\Http\RedirectResponse
    {
        $bingo = $this->bingoService->createBingo($request->all());

        if ($bingo) {
            return Redirect::route('admin.bingos.show', [$bingo])->with('success', t('Record was created successfully.'));
        }

        return Redirect::route('admin.bingos.index')->with('danger', t('An error occurred when saving record.'));
    }

    /**
     * Bingo show.
     */
    public function show(Bingo $bingo): mixed
    {
        if (! CheckPermission::handle('read', $bingo)) {
            return Redirect::route('admin.bingos.index');
        }

        $bingo->load('words');

        return View::make('admin.bingo.show', compact('bingo'));
    }

    /**
     * Edit bingo.
     */
    public function edit(Bingo $bingo): \Illuminate\Contracts\View\View
    {
        $bingo->load(['words', 'project']);
        $projects = $this->projectService->getProjectEventSelect();

        return View::make('admin.bingo.edit', compact('bingo', 'projects'));
    }

    /**
     * Update bingo.
     */
    public function update(BingoFormRequest $request, Bingo $bingo): \Illuminate\Http\RedirectResponse
    {
        if (! CheckPermission::handle('update', $bingo)) {
            return Redirect::route('admin.bingos.index');
        }

        $this->bingoService->updateBingo($bingo, $request->all());

        return Redirect::route('admin.bingos.show', [$bingo])->with('success', t('Record was updated successfully.'));
    }

    /**
     * Delete bingo.
     */
    public function destroy(Bingo $bingo): \Illuminate\Http\RedirectResponse
    {
        if (! CheckPermission::handle('delete', $bingo)) {
            return Redirect::route('admin.bingos.index');
        }

        $result = $bingo->delete();

        if ($result) {
            return Redirect::route('admin.bingos.index')->with('success', t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));
        }

        return Redirect::route('admin.bingos.edit', [$bingo])->with('danger', t('An error occurred when deleting record.'));
    }
}
