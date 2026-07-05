<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ForumAnswer;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumAnswerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ForumAnswer');
    }

    public function view(AuthUser $authUser, ForumAnswer $forumAnswer): bool
    {
        return $authUser->can('View:ForumAnswer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ForumAnswer');
    }

    public function update(AuthUser $authUser, ForumAnswer $forumAnswer): bool
    {
        return $authUser->can('Update:ForumAnswer');
    }

    public function delete(AuthUser $authUser, ForumAnswer $forumAnswer): bool
    {
        return $authUser->can('Delete:ForumAnswer');
    }

    public function restore(AuthUser $authUser, ForumAnswer $forumAnswer): bool
    {
        return $authUser->can('Restore:ForumAnswer');
    }

    public function forceDelete(AuthUser $authUser, ForumAnswer $forumAnswer): bool
    {
        return $authUser->can('ForceDelete:ForumAnswer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ForumAnswer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ForumAnswer');
    }

    public function replicate(AuthUser $authUser, ForumAnswer $forumAnswer): bool
    {
        return $authUser->can('Replicate:ForumAnswer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ForumAnswer');
    }

}
