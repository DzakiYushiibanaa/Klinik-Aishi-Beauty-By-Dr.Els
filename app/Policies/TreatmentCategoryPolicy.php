<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TreatmentCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class TreatmentCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TreatmentCategory');
    }

    public function view(AuthUser $authUser, TreatmentCategory $treatmentCategory): bool
    {
        return $authUser->can('View:TreatmentCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TreatmentCategory');
    }

    public function update(AuthUser $authUser, TreatmentCategory $treatmentCategory): bool
    {
        return $authUser->can('Update:TreatmentCategory');
    }

    public function delete(AuthUser $authUser, TreatmentCategory $treatmentCategory): bool
    {
        return $authUser->can('Delete:TreatmentCategory');
    }

    public function restore(AuthUser $authUser, TreatmentCategory $treatmentCategory): bool
    {
        return $authUser->can('Restore:TreatmentCategory');
    }

    public function forceDelete(AuthUser $authUser, TreatmentCategory $treatmentCategory): bool
    {
        return $authUser->can('ForceDelete:TreatmentCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TreatmentCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TreatmentCategory');
    }

    public function replicate(AuthUser $authUser, TreatmentCategory $treatmentCategory): bool
    {
        return $authUser->can('Replicate:TreatmentCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TreatmentCategory');
    }

}