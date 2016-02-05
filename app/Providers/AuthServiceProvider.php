<?php

namespace Biospex\Providers;

use Biospex\Models\Group;
use Biospex\Models\User;
use Biospex\Models\Project;
use Biospex\Models\Expedition;
use Biospex\Policies\UserPolicy;
use Biospex\Policies\GroupPolicy;
use Biospex\Policies\ProjectPolicy;
use Biospex\Policies\ExpeditionPolicy;
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
        Project::class => ProjectPolicy::class,
        Expedition::class => ExpeditionPolicy::class,
        User::class => UserPolicy::class
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
