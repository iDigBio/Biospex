<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TeamCategoryContract;

class TeamsController extends Controller
{

    /**
     * @var TeamCategoryContract
     */
    public $teamCategoryContract;

    /**
     * TeamsController constructor.
     * 
     * @param TeamCategoryContract $teamCategoryContract
     */
    public function __construct(TeamCategoryContract $teamCategoryContract)
    {

        $this->teamCategoryContract = $teamCategoryContract;
    }

    /**
     * Show categories.
     * 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $categories = $this->teamCategoryContract->with('teams')
            ->orderBy('id', 'asc')
            ->groupBy('id')
            ->findAll();

        return view('frontend.teams.index', compact('categories'));
    }
}
