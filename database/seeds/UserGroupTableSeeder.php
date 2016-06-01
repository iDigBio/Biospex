<?php

use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class UserGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        
        $groupRepo = app(\App\Repositories\Contracts\Group::class);
        $userRepo = app(\App\Repositories\Contracts\User::class);
        
        $names = ['Users',  'Herbarium', 'Calbug', 'Admins'];
        $groups = [];
        
        foreach ($names as $name)
        {
            $groups[$name] = $groupRepo->where(['name' => $name])->first();
        }
        
        $users = $userRepo->all();
        foreach ($users as $user) {
            foreach ($names as $name)
            {
                if ($name === 'Admins' && $user->email === 'biospex@gmail.com')
                {
                    $user->assignGroup($groups[$name]);
                }
                else
                {
                    $user->assignGroup($groups[$name]);
                }
            }
        }
    }
}
