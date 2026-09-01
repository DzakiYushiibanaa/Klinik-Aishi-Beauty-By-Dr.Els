<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PatientForm;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientFormPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PatientForm');
    }

    public function view(AuthUser $authUser, PatientForm $patientForm): bool
    {
        return $authUser->can('View:PatientForm');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PatientForm');
    }

    public function update(AuthUser $authUser, PatientForm $patientForm): bool
    {
        return $authUser->can('Update:PatientForm');
    }

    public function delete(AuthUser $authUser, PatientForm $patientForm): bool
    {
        return $authUser->can('Delete:PatientForm');
    }

    public function restore(AuthUser $authUser, PatientForm $patientForm): bool
    {
        return $authUser->can('Restore:PatientForm');
    }

    public function forceDelete(AuthUser $authUser, PatientForm $patientForm): bool
    {
        return $authUser->can('ForceDelete:PatientForm');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PatientForm');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PatientForm');
    }

    public function replicate(AuthUser $authUser, PatientForm $patientForm): bool
    {
        return $authUser->can('Replicate:PatientForm');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PatientForm');
    }

}