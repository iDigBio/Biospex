<?php
use Zizaco\FactoryMuff\Facade\FactoryMuff;

class UserTest extends TestCase
{
    public function testOwnsGroups()
    {
        // Instantiate, fill with values, save and return
        $group = FactoryMuff::create('Group');

        $this->assertEquals($group->user_id, $group->owner->id);

    }
}