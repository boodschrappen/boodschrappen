<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
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
    public function update(?User $user, Store $store): bool
    {
        return $user?->is_admin ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Store $store): bool
    {
        return $user?->is_admin ?? false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Store $store): bool
    {
        return $user?->is_admin ?? false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Store $store): bool
    {
        return $user?->is_admin ?? false;
    }
}
