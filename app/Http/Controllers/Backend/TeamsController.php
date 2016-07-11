<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamCategoryFormRequest;
use App\Http\Requests\TeamFormRequest;
use App\Repositories\Contracts\Team;
use App\Repositories\Contracts\User;
use Illuminate\Http\Request;
use App\Repositories\Contracts\TeamCategory;

class TeamsController extends Controller
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @var TeamCategory
     */
    private $category;

    /**
     * @var Team
     */
    private $team;

    /**
     * TeamsController constructor.
     *
     * @param Request $request
     * @param User $user
     * @param TeamCategory $category
     * @param Team $team
     */
    public function __construct(Request $request, User $user, TeamCategory $category, Team $team)
    {
        $this->request = $request;
        $this->user = $user;
        $this->category = $category;
        $this->team = $team;
    }

    /**
     * Show team forms and list by category.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->category->pluck('name', 'id')->toArray();
        $categories = $this->category->with(['teams'])->groupBy('id')->get();
        $categoryId = null;
        $teamId = null;

        return view('backend.teams.index', compact('user', 'categories', 'select', 'categoryId', 'teamId'));
    }

    /**
     * Show create forms for team category and members.
     *
     * @param Request $request
     * @param $categoryId
     * @return mixed
     */
    public function create(Request $request, $categoryId)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->category->pluck('name', 'id')->toArray();
        $categories = $this->category->with(['teams'])->groupBy('id')->get();

        return view('backend.teams.index', compact('user', 'categories', 'select', 'categoryId'));
    }

    /**
     * Create Team member.
     *
     * @param TeamFormRequest $request
     * @return mixed
     */
    public function store(TeamFormRequest $request)
    {
        $team = $this->team->create($request->all());

        $team ?
            Toastr::success('Team member has been created successfully.', 'Team Member Create') :
            Toastr::error('Team member could not be saved.', 'Team member Create');

        return redirect()->route('admin.teams.create', $request->get('team_category_id'));
    }

    /**
     * Store Category.
     *
     * @param TeamCategoryFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCategory(TeamCategoryFormRequest $request)
    {
        $category = $this->category->create(['name' => $request->get('name')]);

        $category ?
            Toastr::success('Team category has been created successfully.', 'Team Category Create') :
            Toastr::error('Team category could not be saved.', 'Team Category Create');

        return redirect()->route('admin.teams.index', $category->id);
    }

    /**
     * Edit team.
     *
     * @param Request $request
     * @param $categoryId
     * @param $teamId
     * @return mixed
     */
    public function edit(Request $request, $categoryId, $teamId)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->category->pluck('name', 'id')->toArray();
        $categories = $this->category->with(['teams'])->groupBy('id')->get();
        $team = $this->team->find($teamId);

        return view('backend.teams.index', compact('user', 'categories', 'select', 'categoryId', 'team'));
    }

    /**
     * Update team member.
     *
     * @param TeamFormRequest $request
     * @param $categoryId
     * @param $teamId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TeamFormRequest $request, $categoryId, $teamId)
    {
        $team = $this->team->update($request->all(), $teamId);

        $team ? Toastr::success('Team member has been updated successfully.', 'Team Member Update') :
            Toastr::error('Team member could not be updated.', 'Team Member Update');

        return redirect()->route('admin.teams.index');
    }

    /**
     * Edit Category.
     *
     * @param Request $request
     * @param $categoryId
     * @param $teamId
     * @return mixed
     */
    public function editCategory(Request $request, $categoryId, $teamId)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->category->pluck('name', 'id')->toArray();
        $categories = $this->category->with(['teams'])->groupBy('id')->get();
        $category = $this->category->find($categoryId);

        return view('backend.teams.index', compact('user', 'select', 'category', 'categories', 'categoryId', 'teamId'));
    }

    /**
     * Update category.
     *
     * @param TeamCategoryFormRequest $request
     * @param $categoryId
     * @return mixed
     */
    public function updateCategory(TeamCategoryFormRequest $request, $categoryId)
    {
        $category = $this->category->update(['name' => $request->get('name')], $categoryId);

        $category ? Toastr::success('Team category has been updated successfully.', 'Team Category Update')
            : Toastr::error('Team category could not be updated.', 'Team Category Update');

        return redirect()->route('admin.teams.index');
    }

    /**
     * Delete resource.
     * 
     * @param $categoryId
     * @param $teamId
     * @return mixed
     */
    public function delete($categoryId, $teamId)
    {
        if ((int) $teamId === 0)
        {
            $result = $this->category->delete($categoryId);
            $result ? Toastr::success('The category and all team members have been deleted.', 'Team Category Delete')
                : Toastr::error('Team category could not be deleted.', 'Team Category Delete');
        }
        else
        {
            $result = $this->team->delete($teamId);
            $result ? Toastr::success('The team member has been deleted.', 'Team Member Delete')
                : Toastr::error('The team member could not be deleted.', 'Team Member Delete');
        }

        return redirect()->route('admin.teams.index');
    }

}