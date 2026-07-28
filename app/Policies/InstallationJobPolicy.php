<?php

namespace App\Policies;

use App\Enums\JobStage;
use App\Models\InstallationJob;
use App\Models\User;

class InstallationJobPolicy
{
    /**
     * Admin/staff see all jobs; technicians see only their "My Jobs" list (filtered in the query, not here).
     * See docs/04-api-backend-contract.md §5 Authorization Matrix.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isTechnician();
    }

    public function view(User $user, InstallationJob $job): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        if ($user->isTechnician()) {
            return $this->isAssigned($user, $job);
        }

        return $user->isCustomer() && $job->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function manageTeam(User $user, InstallationJob $job): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    /**
     * Admin/staff can move a job to any stage. A technician may only advance
     * their own assigned job to the immediate next stage (sequential only).
     */
    public function advanceStage(User $user, InstallationJob $job, ?JobStage $toStage = null): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        if (! $user->isTechnician() || ! $this->isAssigned($user, $job)) {
            return false;
        }

        return $toStage === null || $toStage === $job->current_stage->next();
    }

    public function uploadDocument(User $user, InstallationJob $job): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        return $user->isTechnician() && $this->isAssigned($user, $job);
    }

    private function isAssigned(User $user, InstallationJob $job): bool
    {
        return $job->activeTeamAssignments()->where('user_id', $user->id)->exists();
    }
}
