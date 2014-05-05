<?php
/**
 * GroupTest.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    BSD License (3-clause)
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 */

use Zizaco\FactoryMuff\Facade\FactoryMuff;

// use Cartalyst\Sentry\Sentry;
// new Cartalyst\Sentry\Sentry; // Prevent RunTime Exception: A hasher has not been provided for the user.
// $user = FactoryMuff::create('Cartalyst\Sentry\Users\Eloquent\User');

class GroupTest extends TestCase
{
    public function testOwnerRelation()
    {
        // Instantiate, fill with values, save and return
        $group = FactoryMuff::create('Group');

        // Does $group have a group
        $this->assertEquals($group->user_id, $group->owner->id);

    }

}