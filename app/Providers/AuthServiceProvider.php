<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Like;
use App\Models\MuteWord;
use App\Models\MuteUser;
use App\Policies\UserPolicy;
use App\Policies\ThreadPolicy;
use App\Policies\PostPolicy;
use App\Policies\LikePolicy;
use App\Policies\MuteWordPolicy;
use App\Policies\MuteUserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
        Thread::class => ThreadPolicy::class,
        Post::class => PostPolicy::class,
        Like::class => LikePolicy::class,
        MuteWord::class => MuteWordPolicy::class,
        MuteUser::class => MuteUserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
