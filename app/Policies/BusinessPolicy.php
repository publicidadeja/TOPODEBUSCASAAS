<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function update(User $user, Business $business)
    {
        return $user->id === $business->user_id;
    }

    public function delete(User $user, Business $business)
    {
        return $user->id === $business->user_id;
    }
}