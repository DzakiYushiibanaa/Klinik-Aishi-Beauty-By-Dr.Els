<?php

namespace App\Filament\Resources\ForumQuestions\Pages;

use App\Filament\Resources\ForumQuestions\ForumQuestionResource;
use App\Models\ForumQuestion;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListForumQuestions extends ListRecords
{
    protected static string $resource = ForumQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Pertanyaan'),

            'belum_dijawab' => Tab::make('Belum Dijawab')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('answers'))
                ->badge(fn () => ForumQuestion::whereDoesntHave('answers')->count())
                ->badgeColor('danger'),

            'terjawab' => Tab::make('Terjawab')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('answers')),
        ];
    }
}
