<?php

use Biospex\Repositories\Contracts\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $users = $this->getUsers();

        foreach ($users as $user) {
            $this->user->create($user);
        }
    }

    private function getUsers()
    {
        return [
            [
                'email'         => 'admin@biospex.org',
                'password'      => 'biospex',
                'first_name'    => 'Biospex',
                'last_name'     => 'Admin',
            ],
            [
                'email'    => 'biospex@gmail.com',
                'password' => 'biospex',
                'first_name'    => 'Robert',
                'last_name'     => 'Bruhn',
            ],

        ];
    }
}
