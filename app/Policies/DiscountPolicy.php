<?php

namespace App\Policies;

use App\Models\Discount;
use App\Models\User;

class DiscountPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user?->is_admin ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Discount $discount): bool
    {
        return $user?->is_admin ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Discount $discount): bool
    {
        return $user?->is_admin ?? false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Discount $discount): bool
    {
        return $user?->is_admin ?? false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Discount $discount): bool
    {
        return $user?->is_admin ?? false;
    }
}
