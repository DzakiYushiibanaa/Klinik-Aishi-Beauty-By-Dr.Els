<?php

namespace App\Filament\Resources\ForumAnswers\Pages;

use App\Filament\Resources\ForumAnswers\ForumAnswerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForumAnswer extends EditRecord
{
    protected static string $resource = ForumAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
