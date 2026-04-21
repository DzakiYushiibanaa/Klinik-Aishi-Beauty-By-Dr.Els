<?php

namespace App\Filament\Resources\ForumQuestions\Pages;

use App\Filament\Resources\ForumQuestions\ForumQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListForumQuestions extends ListRecords
{
    protected static string $resource = ForumQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
