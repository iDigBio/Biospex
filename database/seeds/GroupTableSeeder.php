<?php

use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Seeder;

class GroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Config::get('config.group_permissions');

        Sentry::getGroupProvider()->create(array(
            'user_id'     => 1,
            'name'        => 'Admins',
            'permissions' => array(
             'superuser'  => 1,
            )));

        Sentry::getGroupProvider()->create(array(
            'user_id'     => 1,
            'name'        => 'Users',
            'permissions' => $permissions));

        Sentry::getGroupProvider()->create(array(
            'user_id'     => 2,
            'name'        => 'Herbarium',
            'permissions' => array()));

        Sentry::getGroupProvider()->create(array(
            'user_id'     => 2,
            'name'        => 'Calbug',
            'permissions' => array()));
    }
}
