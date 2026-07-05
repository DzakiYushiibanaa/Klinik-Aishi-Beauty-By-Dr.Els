<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JadwalPraktik;
use Illuminate\Auth\Access\HandlesAuthorization;

class JadwalPraktikPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JadwalPraktik');
    }

    public function view(AuthUser $authUser, JadwalPraktik $jadwalPraktik): bool
    {
        return $authUser->can('View:JadwalPraktik');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JadwalPraktik');
    }

    public function update(AuthUser $authUser, JadwalPraktik $jadwalPraktik): bool
    {
        return $authUser->can('Update:JadwalPraktik');
    }

    public function delete(AuthUser $authUser, JadwalPraktik $jadwalPraktik): bool
    {
        return $authUser->can('Delete:JadwalPraktik');
    }

    public function restore(AuthUser $authUser, JadwalPraktik $jadwalPraktik): bool
    {
        return $authUser->can('Restore:JadwalPraktik');
    }

    public function forceDelete(AuthUser $authUser, JadwalPraktik $jadwalPraktik): bool
    {
        return $authUser->can('ForceDelete:JadwalPraktik');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JadwalPraktik');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JadwalPraktik');
    }

    public function replicate(AuthUser $authUser, JadwalPraktik $jadwalPraktik): bool
    {
        return $authUser->can('Replicate:JadwalPraktik');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JadwalPraktik');
    }

}
