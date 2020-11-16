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

use Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\BingoFormRequest;
use App\Services\Model\BingoService;
use App\Services\Model\ProjectService;
use Illuminate\Support\Facades\Auth;

/**
 * Class BingoController
 *
 * @package App\Http\Controllers\Admin
 */
class BingoController extends Controller
{
    /**
     * @var \App\Services\Model\BingoService
     */
    private $bingoService;

    /**
     * @var \App\Services\Model\ProjectService
     */
    private $projectService;

    /**
     * BingoController constructor.
     *
     * @param \App\Services\Model\BingoService $bingoService
     * @param \App\Services\Model\ProjectService $projectService
     */
    public function __construct(BingoService $bingoService, ProjectService $projectService)
    {
        $this->bingoService = $bingoService;
        $this->projectService = $projectService;
    }

    /**
     * Display admin index for bingo games created by user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingoService->getAdminIndex(Auth::user()->id);

        return view('admin.bingo.index', compact('bingos'));
    }

    /**
     * Create bingo.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $projects = $this->projectService->getProjectEventSelect();

        return view('admin.bingo.create', compact('projects'));
    }

    /**
     * Store bingo.
     *
     * @param \App\Http\Requests\BingoFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(BingoFormRequest $request)
    {
        $bingo = $this->bingoService->createBingo($request->all());

        if ($bingo) {
            Flash::success(t('Record was created successfully.'));

            return redirect()->route('admin.bingos.show', [$bingo->id]);
        }

        Flash::error(t('An error occurred when saving record.'));

        return redirect()->route('admin.bingos.index');
    }

    /**
     * Bingo show.
     *
     * @param string $bingoId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(string $bingoId)
    {
        $bingo = $this->bingoService->findWith($bingoId, ['words']);

        if ( ! $this->checkPermissions('read', $bingo))
        {
            return redirect()->route('admin.bingos.index');
        }

        return view('admin.bingo.show', compact('bingo'));
    }

    /**
     * Edit bingo.
     *
     * @param string $bingoId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(string $bingoId)
    {
        $bingo = $this->bingoService->findWith($bingoId, ['words', 'project']);
        $projects = $this->projectService->getProjectEventSelect();

        return view('admin.bingo.edit', compact('bingo', 'projects'));
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
        $bingo = $this->bingoService->findWith($bingoId, ['words']);

        if ( ! $this->checkPermissions('update', $bingo))
        {
            return redirect()->route('admin.bingos.index');
        }

        $result = $this->bingoService->updatebingo($request->all(), $bingoId);

        if ($result) {
            Flash::success(t('Record was updated successfully.'));

            return redirect()->route('admin.bingos.show', [$bingoId]);
        }

        Flash::error(t('Error while updating record.'));

        return redirect()->route('admin.bingos.edit', [$bingoId]);
    }

    /**
     * Delete bingo.
     *
     * @param string $bingoId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(string $bingoId)
    {
        $bingo = $this->bingoService->find($bingoId);

        if ( ! $this->checkPermissions('delete', $bingo))
        {
            return redirect()->route('admin.bingos.index');
        }

        $result = $bingo->delete();

        if ($result)
        {
            Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

            return redirect()->route('admin.bingos.index');
        }

        Flash::error(t('An error occurred when deleting record.'));

        return redirect()->route('admin.bingos.edit', [$bingoId]);
    }
}