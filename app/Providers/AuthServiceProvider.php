<?php

namespace App\Providers;

use App\Models\Bingo;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use App\Policies\BingoPolicy;
use App\Policies\EventPolicy;
use App\Policies\UserPolicy;
use App\Policies\GroupPolicy;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Group::class => GroupPolicy::class,
        User::class => UserPolicy::class,
        Event::class => EventPolicy::class,
        Bingo::class => BingoPolicy::class
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);
    }
}
