<?php
/**
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
use App\Repositories\Interfaces\Bingo;
use App\Repositories\Interfaces\Project;
use Illuminate\Support\Facades\Auth;

class BingosController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Bingo
     */
    private $bingoContract;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * BingosController constructor.
     *
     * @param \App\Repositories\Interfaces\Bingo $bingoContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     */
    public function __construct(Bingo $bingoContract, Project $projectContract)
    {
        $this->bingoContract = $bingoContract;
        $this->projectContract = $projectContract;
    }

    /**
     * Display admin index for bingo games created by user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingoContract->getAdminIndex(Auth::user()->id);

        return view('admin.bingo.index', compact('bingos'));
    }

    /**
     * Create bingo.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $projects = $this->projectContract->getProjectEventSelect();

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
        $bingo = $this->bingoContract->createBingo($request->all());

        if ($bingo) {
            Flash::success(trans('messages.record_created'));

            return redirect()->route('admin.bingos.show', [$bingo->id]);
        }

        Flash::error(trans('messages.record_save_error'));

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
        $bingo = $this->bingoContract->findWith($bingoId, ['words']);

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
        $bingo = $this->bingoContract->findWith($bingoId, ['words', 'project']);
        $projects = $this->projectContract->getProjectEventSelect();

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
        $bingo = $this->bingoContract->findWith($bingoId, ['words']);

        if ( ! $this->checkPermissions('update', $bingo))
        {
            return redirect()->route('admin.bingos.index');
        }

        $result = $this->bingoContract->updatebingo($request->all(), $bingoId);

        if ($result) {
            Flash::success(trans('messages.record_updated'));

            return redirect()->route('admin.bingos.show', [$bingoId]);
        }

        Flash::error(trans('messages.record_updated_error'));

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
        $bingo = $this->bingoContract->find($bingoId);

        if ( ! $this->checkPermissions('delete', $bingo))
        {
            return redirect()->route('admin.bingos.index');
        }

        $result = $this->bingoContract->delete($bingo);

        if ($result)
        {
            Flash::success(trans('messages.record_deleted'));

            return redirect()->route('admin.bingos.index');
        }

        Flash::error(trans('messages.record_delete_error'));

        return redirect()->route('admin.bingos.edit', [$bingoId]);
    }
}