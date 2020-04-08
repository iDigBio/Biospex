<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\Interfaces\Bingo;
use App\Repositories\Interfaces\Project;
use Illuminate\Support\Facades\Auth;

class BingosController
{
    /**
     * @var \App\Repositories\Interfaces\Bingo
     */
    private $bingo;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $project;

    /**
     * BingosController constructor.
     *
     * @param \App\Repositories\Interfaces\Bingo $bingo
     * @param \App\Repositories\Interfaces\Project $project
     */
    public function __construct(Bingo $bingo, Project $project)
    {
        $this->bingo = $bingo;
        $this->project = $project;
    }

    /**
     * Display admin index for bingo games created by user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingo->getAdminIndex(Auth::user()->id);

        return view('admin.bingo.index', compact('bingos'));
    }

    /**
     * Create bingo.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $projects = $this->project->getProjectEventSelect();

        return view('admin.bingo.create', compact('projects'));
    }

    public function store()
    {

    }

    public function read()
    {

    }

    /**
     * Edit bingo.
     *
     * @param string $bingoId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(string $bingoId)
    {
        $bingo = $this->bingo->findWith($bingoId, ['words', 'project']);
        $projects = $this->project->getProjectEventSelect();

        return view('admin.bingo.edit', compact('bingo', 'projects'));
    }

    public function update()
    {

    }

    public function delete()
    {

    }

    public function generate(string $bingoId)
    {
        
    }
}