<?php

namespace App\Filament\Resources\ForumQuestions;

use App\Filament\Resources\ForumQuestions\Pages\ListForumQuestions;
use App\Filament\Resources\ForumQuestions\Schemas\ForumQuestionForm;
use App\Filament\Resources\ForumQuestions\Tables\ForumQuestionsTable;
use App\Models\ForumQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ForumQuestionResource extends Resource
{
    protected static ?string $model = ForumQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Forum Tanya Jawab';

    protected static ?string $pluralLabel = 'Forum Tanya Jawab';

    protected static ?string $modelLabel = 'Pertanyaan';

    public static function form(Schema $schema): Schema
    {
        return ForumQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ForumQuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForumQuestions::route('/'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }
}
