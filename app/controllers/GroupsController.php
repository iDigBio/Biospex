<?php
/**
 * GroupsController.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

use Biospex\Repo\Group\GroupInterface;
use Biospex\Form\Group\GroupForm;
use Biospex\Repo\Permission\PermissionInterface;
use Biospex\Repo\Invite\InviteInterface;
use Biospex\Form\Invite\InviteForm;


class GroupsController extends BaseController {

    /**
     * @var Biospex\Repo\Group\GroupInterface
     */
    protected $group;

    /**
     * @var Biospex\Form\Group\GroupForm
     */
    protected $groupForm;

    /**
     * @var Biospex\Repo\Permission\PermissionInterface
     */
    protected $permission;

    /**
     * @var Biospex\Repo\Invite\InviteInterface
     */
    protected $invite;

    /**
     * @var Biospex\Form\Invite\InviteForm
     */
    protected $inviteForm;

	/**
	 * Constructor
	 */
	public function __construct(
        GroupInterface $group,
        GroupForm $groupForm,
        PermissionInterface $permission,
        InviteInterface $invite,
        InviteForm $inviteForm
    )
	{
		$this->group = $group;
		$this->groupForm = $groupForm;
        $this->permission = $permission;
        $this->invite = $invite;
        $this->inviteForm = $inviteForm;

		// Establish Filters
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('hasGroupAccess:group_view', array('only' => array('show', 'index')));
        $this->beforeFilter('hasGroupAccess:group_edit', array('only' => array('edit', 'update')));
        $this->beforeFilter('hasGroupAccess:group_delete', array('only' => array('destroy')));
        $this->beforeFilter('hasGroupAccess:group_create', array('only' => array('create')));
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        // Find the user and retrieve groups
        $user = Sentry::getUser();
        $isSuperUser = $user->isSuperUser();
        $groups = $isSuperUser ? $this->group->all() : $user->getGroups();

        foreach ($groups as $key => $group)
        {
            if (in_array($group->id, array(1,2)) && ! $isSuperUser)
                unset($groups[$key]);
        }

		return View::make('groups.index', compact('groups', 'user', 'isSuperUser'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        $user = Sentry::getUser();
		return View::make('groups.create', compact('user'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		// Form Processing
        $result = $this->groupForm->save( Input::all() );
        
        if( $result['success'] )
        {
            $user = Sentry::getUser();
            // Assign the group to the user
            if ($user->addGroup($result['group']))
            {
                Event::fire('group.created');

                // Success!
                Session::flash('success', $result['message']);
                return Redirect::action('GroupsController@index');
            }
            else
            {
                Session::flash('error', 'groups.useradderror');
                return Redirect::action('GroupsController@create')
                    ->withInput()
                    ->withErrors( $this->groupForm->errors() );
            }
        } else {
            Session::flash('error', $result['message']);
            return Redirect::action('GroupsController@create')
                ->withInput()
                ->withErrors( $this->groupForm->errors() );
        }
	}

	/**
	 * Display the specified resource.
	 *
	 * @return Response
	 */
	public function show($id)
	{
        // Get all available permissions
        $permissions = $this->permission->all();

		//Show a group and its permissions. 
		$group = $this->group->find($id);

        $viewPermissions = Sentry::getUser()->hasAccess('permission_view');

		return View::make('groups.show')->with(array(
            'group' => $group,
            'permissions' => $permissions,
            'viewPermissions' => $viewPermissions
        ));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @return Response
	 */
	public function edit($id)
	{
        // Get all available permissions
        $permissions = $this->permission->getPermissionsGroupBy();

        $editPermissions = Sentry::getUser()->hasAccess('permission_edit');

		$group = $this->group->find($id);
		return View::make('groups.edit')->with(array(
            'group' => $group,
            'permissions' => $permissions,
            'editPermissions' => $editPermissions
        ));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @return Response
	 */
	public function update($id)
	{
		// Form Processing
        $result = $this->groupForm->update( Input::all() );

        if( $result['success'] )
        {
            Event::fire('group.updated', array(
                'groupId' => $id, 
            ));

            // Success!
            Session::flash('success', $result['message']);
            return Redirect::action('GroupsController@index');

        } else {
            Session::flash('error', $result['message']);
            return Redirect::action('GroupsController@create')
                ->withInput()
                ->withErrors( $this->groupForm->errors() );
        }
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @return Response
	 */
	public function destroy($id)
	{
		if ($this->group->destroy($id))
		{
			Event::fire('group.destroyed', array(
                'groupId' => $id, 
            ));

			Session::flash('success', trans('groups.group_destroyed'));
            return Redirect::action('GroupsController@index');
        }
        else 
        {
        	Session::flash('error', trans('groups.group_destroyed_failed'));
            return Redirect::action('GroupsController@index');
        }
	}

    public function invite($id)
    {
        $group = $this->group->find($id);
        return View::make('groups.invite', compact('group'));

    }

    public function sendInvite($id)
    {
        $emails = explode(',', Input::get('emails'));
        foreach ($emails as $email)
        {
            $data = array(
                'group_id' => $id,
                'email' => trim($email),
                'code' => str_random(10)
            );
            if (!$result = $this->inviteForm->save($data))
            {
                Session::flash('warning', trans('groups.send_invite_error', $email));
            }
        }



            Session::flash('warning', trans('groups.il'));
            return Redirect::action('GroupsController@index');

        return "Invite by email";
    }

    /**
     * Add user to group if invite code exists
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function invited()
    {
        $invite = $this->invite->findByCode(Input::get('invite'));

        if ($invite)
        {
            $user = Sentry::getUser();

            if ($invite->email == $user->email)
            {
                $group = Sentry::findGroupById($invite->group_id);
                if ($result = $user->addGroup($group))
                {
                    $this->invite->destroy($invite->id);
                    Session::flash('success', trans('groups.group_joined'));
                    return Redirect::action('GroupsController@index');
                }
            }
        }


        Session::flash('warning', trans('groups.invite_fail'));
        return Redirect::action('GroupsController@index');
    }

}