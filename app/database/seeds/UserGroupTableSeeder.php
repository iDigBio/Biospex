<?php

class UserGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groupUser = Sentry::getGroupProvider()->findByName('Users');
        $groupHerbarium = Sentry::getGroupProvider()->findByName('Herbarium');
        $groupCalbug = Sentry::getGroupProvider()->findByName('Calbug');
        $groupAdmin = Sentry::getGroupProvider()->findByName('Admins');

        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $sentryUser = Sentry::getUserProvider()->findByLogin($user->email);
            if ($user->email == 'admin@biospex.org') {
                $sentryUser->addGroup($groupAdmin);
            } else {
                $sentryUser->addGroup($groupUser);
                $sentryUser->addGroup($groupHerbarium);
                $sentryUser->addGroup($groupCalbug);
            }
        }
    }
}
