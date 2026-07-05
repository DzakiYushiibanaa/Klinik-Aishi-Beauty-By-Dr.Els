<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Terapis;
use Illuminate\Auth\Access\HandlesAuthorization;

class TerapisPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Terapis');
    }

    public function view(AuthUser $authUser, Terapis $terapis): bool
    {
        return $authUser->can('View:Terapis');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Terapis');
    }

    public function update(AuthUser $authUser, Terapis $terapis): bool
    {
        return $authUser->can('Update:Terapis');
    }

    public function delete(AuthUser $authUser, Terapis $terapis): bool
    {
        return $authUser->can('Delete:Terapis');
    }

    public function restore(AuthUser $authUser, Terapis $terapis): bool
    {
        return $authUser->can('Restore:Terapis');
    }

    public function forceDelete(AuthUser $authUser, Terapis $terapis): bool
    {
        return $authUser->can('ForceDelete:Terapis');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Terapis');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Terapis');
    }

    public function replicate(AuthUser $authUser, Terapis $terapis): bool
    {
        return $authUser->can('Replicate:Terapis');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Terapis');
    }

}
