<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\TeamCategory;

class TeamsController extends Controller
{

    /**
     * @var TeamCategory
     */
    public $teamCategoryContract;

    /**
     * TeamsController constructor.
     * 
     * @param TeamCategory $teamCategoryContract
     */
    public function __construct(TeamCategory $teamCategoryContract)
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
        $categories = $this->teamCategoryContract->getTeamIndexPage();

        return view('frontend.teams.index', compact('categories'));
    }
}
