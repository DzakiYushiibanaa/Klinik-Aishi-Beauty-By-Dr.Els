<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Dokter;
use Illuminate\Auth\Access\HandlesAuthorization;

class DokterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Dokter');
    }

    public function view(AuthUser $authUser, Dokter $dokter): bool
    {
        return $authUser->can('View:Dokter');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Dokter');
    }

    public function update(AuthUser $authUser, Dokter $dokter): bool
    {
        return $authUser->can('Update:Dokter');
    }

    public function delete(AuthUser $authUser, Dokter $dokter): bool
    {
        return $authUser->can('Delete:Dokter');
    }

    public function restore(AuthUser $authUser, Dokter $dokter): bool
    {
        return $authUser->can('Restore:Dokter');
    }

    public function forceDelete(AuthUser $authUser, Dokter $dokter): bool
    {
        return $authUser->can('ForceDelete:Dokter');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Dokter');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Dokter');
    }

    public function replicate(AuthUser $authUser, Dokter $dokter): bool
    {
        return $authUser->can('Replicate:Dokter');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Dokter');
    }

}
