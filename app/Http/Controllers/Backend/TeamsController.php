<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamFormRequest;
use App\Repositories\Contracts\Team;
use App\Repositories\Contracts\User;
use Illuminate\Http\Request;
use App\Repositories\Contracts\TeamCategory;

class TeamsController extends Controller
{

    /**
     * @var TeamCategory
     */
    private $category;
    
    /**
     * @var User
     */
    private $user;

    /**
     * TeamsController constructor.
     *
     * @param User $user
     * @param TeamCategory $category
     */
    public function __construct(User $user, TeamCategory $category)
    {
        $this->category = $category;
        $this->user = $user;
    }

    /**
     * Show team members.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $categories = $this->category->with(['teams'])->get();

        return view('backend.teams.index', compact('user', 'categories'));
    }

    /**
     * Show create forms for team category and members.
     * 
     * @param Request $request
     * @param null $category
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request, $category = null)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $categories = [null => 'Please Select'] + $this->category->pluck('label', 'id')->toArray();

        return view('backend.teams.create', compact('user', 'categories', 'category'));
        
    }

    /**
     * Create Team member.
     *
     * @param TeamFormRequest $request
     * @param Team $repo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TeamFormRequest $request, Team $repo)
    {
        $member = $repo->create($request->all());

        $member ? Toastr::success('Team member has been created successfully.', 'Team Member Create') : Toastr::error('Team member could not be saved.', 'Team Member Create');

        return redirect()->route('admin.teams.index');
    }

    /**
     * Store Category.
     */
    public function storeCategory()
    {
    }

    /**
     *
     */
    public function show()
    {

    }

    /**
     * Edit Category or Faq.
     *
     */
    public function edit(Request $request)
    {
    }

    /**
     * Update FAQ
     */
    public function update()
    {
    }

    /**
     *
     */
    public function updateCategory()
    {
    }

    /**
     * Delete resource.
     */
    public function delete()
    {

    }

}