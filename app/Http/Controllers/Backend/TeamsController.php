<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamCategoryFormRequest;
use App\Http\Requests\TeamFormRequest;
use App\Repositories\Contracts\TeamContract;
use App\Repositories\Contracts\UserContract;
use App\Repositories\Contracts\TeamCategoryContract;

class TeamsController extends Controller
{

    /**
     * @var UserContract
     */
    private $userContract;

    /**
     * @var TeamCategoryContract
     */
    private $teamCategoryContract;

    /**
     * @var TeamContract
     */
    private $teamContract;

    /**
     * TeamsController constructor.
     *
     * @param UserContract $userContract
     * @param TeamCategoryContract $teamCategoryContract
     * @param TeamContract $teamContract
     */
    public function __construct(
        UserContract $userContract,
        TeamCategoryContract $teamCategoryContract,
        TeamContract $teamContract
    )
    {
        $this->userContract = $userContract;
        $this->teamCategoryContract = $teamCategoryContract;
        $this->teamContract = $teamContract;
    }

    /**
     * Show team forms and list by category.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $select = [null => 'Please Select'] + $this->teamCategoryContract->pluck('name', 'id')->toArray();
        $categories = $this->teamCategoryContract->with(['teams'])->groupBy('id')->findAll();
        $categoryId = null;
        $teamId = null;

        return view('backend.teams.index', compact('user', 'categories', 'select', 'categoryId', 'teamId'));
    }

    /**
     * Show create forms for team category and members.
     *
     * @param $categoryId
     * @return mixed
     */
    public function create($categoryId)
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $select = [null => 'Please Select'] + $this->teamCategoryContract->pluck('name', 'id')->toArray();
        $categories = $this->teamCategoryContract->with('teams')->groupBy('id')->findAll();

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
        $team = $this->teamContract->create($request->all());

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
        $category = $this->teamCategoryContract->create(['name' => $request->get('name')]);

        $category ?
            Toastr::success('Team category has been created successfully.', 'Team Category Create') :
            Toastr::error('Team category could not be saved.', 'Team Category Create');

        return redirect()->route('admin.teams.index', $category->id);
    }

    /**
     * Edit team.
     *
     * @param $categoryId
     * @param $teamId
     * @return mixed
     */
    public function edit($categoryId, $teamId)
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $select = [null => 'Please Select'] + $this->teamCategoryContract->pluck('name', 'id')->toArray();
        $categories = $this->teamCategoryContract->with('teams')->groupBy('id')->findAll();
        $team = $this->teamContract->find($teamId);

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
        $team = $this->teamContract->update($teamId, $request->all());

        $team ? Toastr::success('Team member has been updated successfully.', 'Team Member Update') :
            Toastr::error('Team member could not be updated.', 'Team Member Update');

        return redirect()->route('admin.teams.index');
    }

    /**
     * Edit Category.
     *
     * @param $categoryId
     * @param $teamId
     * @return mixed
     */
    public function editCategory($categoryId, $teamId)
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $select = [null => 'Please Select'] + $this->teamCategoryContract->pluck('name', 'id')->toArray();
        $categories = $this->teamCategoryContract->with('teams')->groupBy('id')->findAll();
        $category = $this->teamCategoryContract->find($categoryId);

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
        $category = $this->teamCategoryContract->update($categoryId, ['name' => $request->get('name')]);

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
            $result = $this->teamCategoryContract->delete($categoryId);
            $result ? Toastr::success('The category and all team members have been deleted.', 'Team Category Delete')
                : Toastr::error('Team category could not be deleted.', 'Team Category Delete');
        }
        else
        {
            $result = $this->teamContract->delete($teamId);
            $result ? Toastr::success('The team member has been deleted.', 'Team Member Delete')
                : Toastr::error('The team member could not be deleted.', 'Team Member Delete');
        }

        return redirect()->route('admin.teams.index');
    }

}