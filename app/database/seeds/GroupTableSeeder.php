<?php

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

        Sentry::getGroupProvider()->create([
            'user_id'     => 1,
            'name'        => 'Admins',
            'permissions' => [
                'superuser' => 1,
            ]]);

        Sentry::getGroupProvider()->create([
            'user_id'     => 1,
            'name'        => 'Users',
            'permissions' => $permissions]);

        Sentry::getGroupProvider()->create([
            'user_id'     => 2,
            'name'        => 'Herbarium',
            'permissions' => []]);

        Sentry::getGroupProvider()->create([
            'user_id'     => 2,
            'name'        => 'Calbug',
            'permissions' => []]);
    }
}
