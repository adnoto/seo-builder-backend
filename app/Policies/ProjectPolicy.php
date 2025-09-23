<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'admin', 'editor', 'viewer']);
    }

    /**
     * Determine if the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['owner', 'admin', 'editor', 'viewer']) && $project->user_id === $user->id;
    }

    /**
     * Determine if the user can create projects.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'admin']);
    }

    /**
     * Determine if the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['owner', 'admin']) && $project->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['owner', 'admin']) && $project->user_id === $user->id;
    }
}
?>