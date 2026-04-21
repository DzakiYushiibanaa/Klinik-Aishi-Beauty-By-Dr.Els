<?php

namespace App\Filament\Resources\ForumAnswers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ForumAnswerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('question_id')
                    ->relationship('question', 'title')
                    ->required(),
                Select::make('dokter_id')
                    ->relationship(
                        name: 'dokter',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => 
                            $query->whereHas('dokterProfile', fn($q) => $q->whereIn('status_praktik', ['aktif', 'Aktif']))
                    )
                    ->required(),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
