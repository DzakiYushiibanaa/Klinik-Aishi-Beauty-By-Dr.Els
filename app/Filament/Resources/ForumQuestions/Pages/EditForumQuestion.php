<?php

namespace App\Filament\Resources\ForumQuestions\Pages;

use App\Filament\Resources\ForumQuestions\ForumQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForumQuestion extends EditRecord
{
    protected static string $resource = ForumQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
