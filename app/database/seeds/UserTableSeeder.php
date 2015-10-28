<?php

use Biospex\Repo\User\UserInterface;
use Cartalyst\Sentry\Sentry;

class UserTableSeeder extends Seeder
{
    /**
     * @var Sentry
     */
    protected $sentry;

    /**
     * @param Sentry $sentry
     */
    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = $this->getUsers();

        foreach ($users as $user) {
            $first_name = $user['first_name'];
            $last_name = $user['last_name'];
            unset($user['first_name']);
            unset($user['last_name']);

            $sentryUser = $this->sentry->register($user, true);
            $sentryUser->profile->first_name = $first_name;
            $sentryUser->profile->last_name = $last_name;
            $sentryUser->profile->save();
        }
    }

    private function getUsers()
    {
        return [
            [
                'email'      => 'admin@biospex.org',
                'password'   => 'biospex',
                'first_name' => 'Biospex',
                'last_name'  => 'Admin',
            ],
            [
                'email'      => 'biospex@gmail.com',
                'password'   => 'biospex',
                'first_name' => 'Robert',
                'last_name'  => 'Bruhn',
            ],

        ];
    }
}
