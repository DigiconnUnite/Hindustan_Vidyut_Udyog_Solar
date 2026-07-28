<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    /**
     * Admin/staff only — see docs/04-api-backend-contract.md §5 Authorization Matrix.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }
}
