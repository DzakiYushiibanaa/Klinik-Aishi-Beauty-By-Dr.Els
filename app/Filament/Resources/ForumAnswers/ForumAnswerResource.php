<?php

namespace App\Filament\Resources\ForumAnswers;

use App\Filament\Resources\ForumAnswers\Pages\CreateForumAnswer;
use App\Filament\Resources\ForumAnswers\Pages\EditForumAnswer;
use App\Filament\Resources\ForumAnswers\Pages\ListForumAnswers;
use App\Filament\Resources\ForumAnswers\Schemas\ForumAnswerForm;
use App\Filament\Resources\ForumAnswers\Tables\ForumAnswersTable;
use App\Models\ForumAnswer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ForumAnswerResource extends Resource
{
    protected static ?string $model = ForumAnswer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ForumAnswerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ForumAnswersTable::configure($table);
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
            'index' => ListForumAnswers::route('/'),
            'create' => CreateForumAnswer::route('/create'),
            'edit' => EditForumAnswer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chat-bubble-oval-left-ellipsis';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Forum';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}
