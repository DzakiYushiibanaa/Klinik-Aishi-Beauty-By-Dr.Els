<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Treatment;
use Illuminate\Auth\Access\HandlesAuthorization;

class TreatmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Treatment');
    }

    public function view(AuthUser $authUser, Treatment $treatment): bool
    {
        return $authUser->can('View:Treatment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Treatment');
    }

    public function update(AuthUser $authUser, Treatment $treatment): bool
    {
        return $authUser->can('Update:Treatment');
    }

    public function delete(AuthUser $authUser, Treatment $treatment): bool
    {
        return $authUser->can('Delete:Treatment');
    }

    public function restore(AuthUser $authUser, Treatment $treatment): bool
    {
        return $authUser->can('Restore:Treatment');
    }

    public function forceDelete(AuthUser $authUser, Treatment $treatment): bool
    {
        return $authUser->can('ForceDelete:Treatment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Treatment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Treatment');
    }

    public function replicate(AuthUser $authUser, Treatment $treatment): bool
    {
        return $authUser->can('Replicate:Treatment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Treatment');
    }

}