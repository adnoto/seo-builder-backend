<?php

namespace App\Policies;

use App\Models\ProjectExport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectExportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectExport $export): bool
    {
        // User can view exports for projects they own
        return $user->id === $export->project->user_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectExport $export): bool
    {
        // User can update exports for projects they own
        return $user->id === $export->project->user_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectExport $export): bool
    {
        // User can delete exports for projects they own
        return $user->id === $export->project->user_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectExport $export): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectExport $export): bool
    {
        return $user->hasRole('admin');
    }
}