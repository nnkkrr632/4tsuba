<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function checkUserIsGuest(User $user)
    {
        return $user->role === 'guest';
    }
    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function checkUserIsNotGuest(User $user)
    {
        return $user->role !== 'guest';
    }
}
