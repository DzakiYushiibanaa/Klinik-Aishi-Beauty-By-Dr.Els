<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Apoteker;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApotekerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Apoteker');
    }

    public function view(AuthUser $authUser, Apoteker $apoteker): bool
    {
        return $authUser->can('View:Apoteker');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Apoteker');
    }

    public function update(AuthUser $authUser, Apoteker $apoteker): bool
    {
        return $authUser->can('Update:Apoteker');
    }

    public function delete(AuthUser $authUser, Apoteker $apoteker): bool
    {
        return $authUser->can('Delete:Apoteker');
    }

    public function restore(AuthUser $authUser, Apoteker $apoteker): bool
    {
        return $authUser->can('Restore:Apoteker');
    }

    public function forceDelete(AuthUser $authUser, Apoteker $apoteker): bool
    {
        return $authUser->can('ForceDelete:Apoteker');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Apoteker');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Apoteker');
    }

    public function replicate(AuthUser $authUser, Apoteker $apoteker): bool
    {
        return $authUser->can('Replicate:Apoteker');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Apoteker');
    }

}