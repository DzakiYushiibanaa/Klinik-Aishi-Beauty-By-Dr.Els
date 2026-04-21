<?php

namespace App\Filament\Resources\ForumQuestions\Pages;

use App\Filament\Resources\ForumQuestions\ForumQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForumQuestion extends CreateRecord
{
    protected static string $resource = ForumQuestionResource::class;
}
