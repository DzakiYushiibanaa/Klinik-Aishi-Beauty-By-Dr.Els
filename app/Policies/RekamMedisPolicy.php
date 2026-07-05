<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RekamMedis;
use Illuminate\Auth\Access\HandlesAuthorization;

class RekamMedisPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RekamMedis');
    }

    public function view(AuthUser $authUser, RekamMedis $rekamMedis): bool
    {
        return $authUser->can('View:RekamMedis');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RekamMedis');
    }

    public function update(AuthUser $authUser, RekamMedis $rekamMedis): bool
    {
        return $authUser->can('Update:RekamMedis');
    }

    public function delete(AuthUser $authUser, RekamMedis $rekamMedis): bool
    {
        return $authUser->can('Delete:RekamMedis');
    }

    public function restore(AuthUser $authUser, RekamMedis $rekamMedis): bool
    {
        return $authUser->can('Restore:RekamMedis');
    }

    public function forceDelete(AuthUser $authUser, RekamMedis $rekamMedis): bool
    {
        return $authUser->can('ForceDelete:RekamMedis');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RekamMedis');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RekamMedis');
    }

    public function replicate(AuthUser $authUser, RekamMedis $rekamMedis): bool
    {
        return $authUser->can('Replicate:RekamMedis');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RekamMedis');
    }

}
