<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TeamCategory;

class TeamsController extends Controller
{

    /**
     * @var TeamCategory
     */
    private $category;

    /**
     * TeamsController constructor.
     * 
     * @param TeamCategory $category
     */
    public function __construct(TeamCategory $category)
    {

        $this->category = $category;
    }

    /**
     * Show categories.
     * 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $categories = $this->category->with(['teams'])->orderBy(['id' => 'asc'])->groupBy('id')->get();

        return view('frontend.teams.index', compact('categories'));
    }
}
