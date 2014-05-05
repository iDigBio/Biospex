<?php

use Zizaco\FactoryMuff\Facade\FactoryMuff;

class ProjectsControllerTest extends TestCase {

    protected $mock;

    public function setUp()
    {
        parent::setUp();

        $this->mockUser = $this->mock('Biospex\Repo\User\UserInterface');
        $this->app->instance('User', $this->mockUser);

        $this->mockGroup = $this->mock('Biospex\Repo\Group\GroupInterface');
        $this->app->instance('Group', $this->mockGroup);

        $this->mockSentry = $this->mock('Cartalyst\Sentry\Sentry');
        $this->app->instance('Sentry', $this->mockSentry);

        $this->mockProject = $this->mock('Biospex\Repo\Project\ProjectInterface');
        $this->app->instance('Project', $this->mockProject);

        $this->mockProjectForm = $this->mock('Biospex\Form\Project\ProjectForm');
        $this->app->instance('ProjectForm', $this->mockProjectForm);
    }

    public function testAllAdmin()
    {
        $group = FactoryMuff::create('Group');
        $group->projects = $this->mockProject;
        $this->mockUser->shouldReceive('getUser')->once()->andReturn($this->mockSentry);
        $this->mockSentry->shouldReceive('isSuperUser')->once()->andReturn(true);
        $this->mockGroup->shouldReceive('all')->once()->andReturn(array($group));
        $this->get('projects/all');
        $this->assertViewHas(array('groupProjects'), array('groupNames'));
    }

    public function testAllUser()
    {
        $group = FactoryMuff::create('Group');
        $group->projects = $this->mockProject;
        $this->mockUser->shouldReceive('getUser')->once()->andReturn($this->mockSentry);
        $this->mockSentry->shouldReceive('isSuperUser')->once()->andReturn(false);
        $this->mockSentry->shouldReceive('getGroups')->once()->andReturn(array($group));
        $this->get('projects/all');
        $this->assertViewHas(array('groupProjects'), array('groupNames'));
    }

    public function testCreate()
    {
        $group = FactoryMuff::create('Group');
        $this->mockGroup->shouldReceive('find')->once()->andReturn($group);
        $this->get('groups/1/projects/create');
        $this->assertViewHas(array('group'));
    }

    public function testStoreFail()
    {
        $project = FactoryMuff::create('Project');
        $this->mockProjectForm->shouldReceive('save')->once()->andReturn(false);
        $this->mockProjectForm->shouldReceive('errors')->once();
        $this->post('groups/'.$project->group_id.'/projects');

        $this->assertRedirectedToRoute('groups.projects.create', $project->group_id);
        $this->assertSessionHasErrors();
    }
/*
    public function testStoreSuccess()
    {
        $project = FactoryMuff::create('Project');
        $this->mockProjectForm->shouldReceive('save')->once()->andReturn($project);
        $this->post('groups/'.$project->group_id.'/projects');
        $this->assertRedirectedToRoute('groups.projects.show', array($project->group_id, $project->id));
    }

    public function testShow()
    {
        $project = FactoryMuff::create('Project');
        $this->mockProject->shouldReceive('find')->once()->andReturn($project);
        $this->get('groups/'.$project->group_id.'/projects/'.$project->id);
        $this->assertViewHas('project');
    }

    public function testEdit()
    {
        $project = FactoryMuff::create('Project');
        $this->mockProject->shouldReceive('find')->once()->andReturn($project);
        $this->get('groups/'.$project->group_id.'/projects/'.$project->id.'/edit');
        $this->assertViewHas('project');
    }

    public function testUpdateFail()
    {
        $project = FactoryMuff::create('Project');
        $this->mockProjectForm->shouldReceive('update')->once()->andReturn(false);
        $this->mockProjectForm->shouldReceive('errors')->once();
        $this->put('groups/'.$project->group_id.'/projects/'.$project->id);
        $this->assertRedirectedToRoute('groups.projects.edit', array($project->group_id, $project->id));
        $this->assertSessionHasErrors();
    }

    public function testUpdateSuccess()
    {
        $project = FactoryMuff::create('Project');
        $this->mockProjectForm->shouldReceive('update')->once()->andReturn($project);
        $this->put('groups/'.$project->group_id.'/projects/'.$project->id);
        $this->assertRedirectedToRoute('groups.projects.show', array($project->group_id, $project->id));
    }

    public function testData()
    {
        $project = FactoryMuff::create('Project');
        $this->mockProject->shouldReceive('find')->once()->andReturn($project);
        $this->get('groups/'.$project->group_id.'/projects/'.$project->id.'/data');
        $this->assertViewHas('project');
    }
*/
}
