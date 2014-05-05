<?php

use Zizaco\FactoryMuff\Facade\FactoryMuff;

class ProjectTest extends TestCase
{
    public function testRelationWithGroup()
    {
        // Instantiate, fill with values, save and return
        $project = FactoryMuff::create('Project');

        // Does $project have a group
        $this->assertEquals($project->group_id, $project->group->id);

    }

    public function testGetProjectsByGroupId()
    {
        // Instantiate, fill with values, save and return
        $data = FactoryMuff::create('Project');

        //$project = (new Project())->getProjectsByGroupId($data->group_id);

        $this->assertEquals($data->id, $project->first()->id);

    }
}
