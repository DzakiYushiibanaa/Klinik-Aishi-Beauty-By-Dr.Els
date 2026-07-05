<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ForumQuestion;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumQuestionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ForumQuestion');
    }

    public function view(AuthUser $authUser, ForumQuestion $forumQuestion): bool
    {
        return $authUser->can('View:ForumQuestion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ForumQuestion');
    }

    public function update(AuthUser $authUser, ForumQuestion $forumQuestion): bool
    {
        return $authUser->can('Update:ForumQuestion');
    }

    public function delete(AuthUser $authUser, ForumQuestion $forumQuestion): bool
    {
        return $authUser->can('Delete:ForumQuestion');
    }

    public function restore(AuthUser $authUser, ForumQuestion $forumQuestion): bool
    {
        return $authUser->can('Restore:ForumQuestion');
    }

    public function forceDelete(AuthUser $authUser, ForumQuestion $forumQuestion): bool
    {
        return $authUser->can('ForceDelete:ForumQuestion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ForumQuestion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ForumQuestion');
    }

    public function replicate(AuthUser $authUser, ForumQuestion $forumQuestion): bool
    {
        return $authUser->can('Replicate:ForumQuestion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ForumQuestion');
    }

}
