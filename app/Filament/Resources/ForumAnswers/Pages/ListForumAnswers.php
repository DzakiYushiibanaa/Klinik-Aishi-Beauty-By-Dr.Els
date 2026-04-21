<?php

namespace App\Filament\Resources\ForumAnswers\Pages;

use App\Filament\Resources\ForumAnswers\ForumAnswerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListForumAnswers extends ListRecords
{
    protected static string $resource = ForumAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
