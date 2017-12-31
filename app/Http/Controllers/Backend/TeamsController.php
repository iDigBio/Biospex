<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamCategoryFormRequest;
use App\Http\Requests\TeamFormRequest;
use App\Interfaces\Team;
use App\Interfaces\User;
use App\Interfaces\TeamCategory;

class TeamsController extends Controller
{

    /**
     * @var User
     */
    private $userContract;

    /**
     * @var TeamCategory
     */
    private $teamCategoryContract;

    /**
     * @var Team
     */
    private $teamContract;

    /**
     * TeamsController constructor.
     *
     * @param User $userContract
     * @param TeamCategory $teamCategoryContract
     * @param Team $teamContract
     */
    public function __construct(
        User $userContract,
        TeamCategory $teamCategoryContract,
        Team $teamContract
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
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $select = [null => 'Please Select'] + $this->teamCategoryContract->getTeamCategorySelect();
        $categories = $this->teamCategoryContract->getCategoriesWithTeams();
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
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $select = [null => 'Please Select'] + $this->teamCategoryContract->getTeamCategorySelect();
        $categories = $this->teamCategoryContract->getCategoriesWithTeams();

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
            Flash::success('Team member has been created successfully.') :
            Flash::error('Team member could not be saved.');

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
            Flash::success('Team category has been created successfully.') :
            Flash::error('Team category could not be saved.');

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
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $select = [null => 'Please Select'] + $this->teamCategoryContract->getTeamCategorySelect();
        $categories = $this->teamCategoryContract->getCategoriesWithTeams();
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
        $team = $this->teamContract->update($request->all(), $teamId);

        $team ? Flash::success('Team member has been updated successfully.') :
            Flash::error('Team member could not be updated.');

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
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $select = [null => 'Please Select'] + $this->teamCategoryContract->getTeamCategorySelect();
        $categories = $this->teamCategoryContract->getCategoriesWithTeams();
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
        $category = $this->teamCategoryContract->update(['name' => $request->get('name')], $categoryId);

        $category ? Flash::success('Team category has been updated successfully.')
            : Flash::error('Team category could not be updated.');

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
            $result ? Flash::success('The category and all team members have been deleted.')
                : Flash::error('Team category could not be deleted.');
        }
        else
        {
            $result = $this->teamContract->delete($teamId);
            $result ? Flash::success('The team member has been deleted.')
                : Flash::error('The team member could not be deleted.');
        }

        return redirect()->route('admin.teams.index');
    }

}