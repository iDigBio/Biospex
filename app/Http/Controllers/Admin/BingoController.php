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
use App\Repositories\BingoRepository;
use App\Repositories\ProjectRepository;
use Illuminate\Support\Facades\Auth;

/**
 * Class BingoController
 */
class BingoController extends Controller
{
    private BingoRepository $bingoRepo;

    /**
     * @var \App\Repositories\ProjectRepository
     */
    private $projectRepo;

    /**
     * BingoController constructor.
     */
    public function __construct(BingoRepository $bingoRepo, ProjectRepository $projectRepo)
    {
        $this->bingoRepo = $bingoRepo;
        $this->projectRepo = $projectRepo;
    }

    /**
     * Display admin index for bingo games created by user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingoRepo->getAdminIndex(Auth::user()->id);

        return \View::make('admin.bingo.index', compact('bingos'));
    }

    /**
     * Create bingo.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $projects = $this->projectRepo->getProjectEventSelect();

        return \View::make('admin.bingo.create', compact('projects'));
    }

    /**
     * Store bingo.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(BingoFormRequest $request)
    {
        $bingo = $this->bingoRepo->createBingo($request->all());

        if ($bingo) {
            \Flash::success(t('Record was created successfully.'));

            return \Redirect::route('admin.bingos.show', [$bingo->id]);
        }

        \Flash::error(t('An error occurred when saving record.'));

        return \Redirect::route('admin.bingos.index');
    }

    /**
     * Bingo show.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(string $bingoId)
    {
        $bingo = $this->bingoRepo->findWith($bingoId, ['words']);

        if (! $this->checkPermissions('read', $bingo)) {
            return \Redirect::route('admin.bingos.index');
        }

        return \View::make('admin.bingo.show', compact('bingo'));
    }

    /**
     * Edit bingo.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(string $bingoId)
    {
        $bingo = $this->bingoRepo->findWith($bingoId, ['words', 'project']);
        $projects = $this->projectRepo->getProjectEventSelect();

        return \View::make('admin.bingo.edit', compact('bingo', 'projects'));
    }

    /**
     * Update bingo.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(BingoFormRequest $request, string $bingoId)
    {
        $bingo = $this->bingoRepo->findWith($bingoId, ['words']);

        if (! $this->checkPermissions('update', $bingo)) {
            return \Redirect::route('admin.bingos.index');
        }

        $result = $this->bingoRepo->updatebingo($request->all(), $bingoId);

        if ($result) {
            \Flash::success(t('Record was updated successfully.'));

            return \Redirect::route('admin.bingos.show', [$bingoId]);
        }

        \Flash::error(t('Error while updating record.'));

        return \Redirect::route('admin.bingos.edit', [$bingoId]);
    }

    /**
     * Delete bingo.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(string $bingoId)
    {
        $bingo = $this->bingoRepo->find($bingoId);

        if (! $this->checkPermissions('delete', $bingo)) {
            return \Redirect::route('admin.bingos.index');
        }

        $result = $bingo->delete();

        if ($result) {
            \Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

            return \Redirect::route('admin.bingos.index');
        }

        \Flash::error(t('An error occurred when deleting record.'));

        return \Redirect::route('admin.bingos.edit', [$bingoId]);
    }
}
