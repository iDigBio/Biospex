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
use App\Services\Models\BingoModelService;
use App\Services\Models\ProjectModelService;
use Illuminate\Support\Facades\Auth;
use Redirect;
use View;

/**
 * Class BingoController
 *
 * @package App\Http\Controllers\Admin
 */
class BingoController extends Controller
{
    /**
     * BingoController constructor.
     *
     */
    public function __construct(
        private readonly BingoModelService $bingoModelService,
        private readonly ProjectModelService $projectModelService
    ) {
    }

    /**
     * Display admin index for bingo games created by user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingoModelService->getBingoByUserIdWithRelations(Auth::id(), ['user', 'project', 'words']);

        return View::make('admin.bingo.index', compact('bingos'));
    }

    /**
     * Create bingo.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $projects = $this->projectModelService->getProjectEventSelect();

        return View::make('admin.bingo.create', compact('projects'));
    }

    /**
     * Store bingo.
     *
     * @param \App\Http\Requests\BingoFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(BingoFormRequest $request)
    {
        $bingo = $this->bingoModelService->createBingo($request->all());

        if ($bingo) {
            return Redirect::route('admin.bingos.show', [$bingo->id])->with('success', t('Record was created successfully.'));
        }

        return Redirect::route('admin.bingos.index')->with('error', t('An error occurred when saving record.'));
    }

    /**
     * Bingo show.
     *
     * @param string $bingoId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(string $bingoId)
    {
        $bingo = $this->bingoModelService->findBingoWithRelations($bingoId, ['words']);

        if (! $this->checkPermissions('read', $bingo)) {
            return Redirect::route('admin.bingos.index');
        }

        return View::make('admin.bingo.show', compact('bingo'));
    }

    /**
     * Edit bingo.
     *
     * @param string $bingoId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(string $bingoId)
    {
        $bingo = $this->bingoModelService->findBingoWithRelations($bingoId, ['words', 'project']);
        $projects = $this->projectModelService->getProjectEventSelect();

        return View::make('admin.bingo.edit', compact('bingo', 'projects'));
    }

    /**
     * Update bingo.
     *
     * @param \App\Http\Requests\BingoFormRequest $request
     * @param string $bingoId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(BingoFormRequest $request, string $bingoId)
    {
        $bingo = $this->bingoModelService->findBingoWithRelations($bingoId, ['words']);

        if (! $this->checkPermissions('update', $bingo)) {
            return Redirect::route('admin.bingos.index');
        }

        $this->bingoModelService->updateBingo($request->all(), $bingoId);

        return Redirect::route('admin.bingos.show', [$bingoId])->with('success', t('Record was updated successfully.'));
    }

    /**
     * Delete bingo.
     *
     * @param string $bingoId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(string $bingoId)
    {
        $bingo = $this->bingoModelService->findBingoWithRelations($bingoId);

        if (! $this->checkPermissions('delete', $bingo)) {
            return Redirect::route('admin.bingos.index');
        }

        $result = $bingo->delete();

        if ($result) {
            return Redirect::route('admin.bingos.index')->with('success', t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));
        }


        return Redirect::route('admin.bingos.edit', [$bingoId])->with('error', t('An error occurred when deleting record.'));
    }
}