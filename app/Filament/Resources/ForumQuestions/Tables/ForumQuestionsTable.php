<?php

namespace App\Filament\Resources\ForumQuestions\Tables;

use App\Models\ForumAnswer;
use App\Models\ForumQuestion;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ForumQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                TextColumn::make('user.name')
                    ->label('PASIEN')
                    ->formatStateUsing(function (ForumQuestion $record): HtmlString {
                        if ($record->is_anonymous) {
                            return new HtmlString('<span style="font-style: italic; color: #8A8078;">Anonymous</span>');
                        }
                        return new HtmlString('<span style="color: #111827;">' . e($record->user?->name ?? '-') . '</span>');
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),

                TextColumn::make('topik')
                    ->label('TOPIK & RINCIAN')
                    ->html()
                    ->state(function (ForumQuestion $record) {
                        $title = e($record->title);
                        $body = e(\Illuminate\Support\Str::limit(strip_tags($record->body ?? ''), 80));
                        return '
                            <div style="display: flex; flex-direction: column; max-width: 480px;">
                                <span style="font-weight: 700; font-size: 0.95rem; color: #111827;">'.$title.'</span>
                                <span style="font-size: 0.8125rem; color: #8A8078; margin-top: 2px;">'.$body.'</span>
                            </div>
                        ';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->where('title', 'like', "%{$search}%")
                            ->orWhere('body', 'like', "%{$search}%");
                    }),

                TextColumn::make('created_at')
                    ->label('TANGGAL')
                    ->date('d M Y')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('status_jawaban')
                    ->label('STATUS')
                    ->html()
                    ->state(function (ForumQuestion $record) {
                        $hasAnswer = $record->answers()->exists();
                        if ($hasAnswer) {
                            return '<span style="display:inline-block; padding:4px 12px; border-radius:9999px; background:#D1FAE5; color:#065F46; font-size:0.8125rem; font-weight:500;">Terjawab</span>';
                        }
                        return '<span style="display:inline-block; padding:4px 12px; border-radius:9999px; background:#FEE2E2; color:#991B1B; font-size:0.8125rem; font-weight:500;">Belum Dijawab</span>';
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view_reply')
                    ->label('View & Reply')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->iconPosition('after')
                    ->color('warning')
                    ->link()
                    ->slideOver()
                    ->stickyModalFooter()
                    ->modalHeading('Detail & Balas Pertanyaan')
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Kirim Jawaban')
                    ->modalCancelActionLabel('Batal')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->fillForm(fn (ForumQuestion $record) => [
                        'body' => $record->answers()->latest()->first()?->body ?? '',
                    ])
                    ->schema(fn (ForumQuestion $record) => [
                        Section::make()
                            ->schema([
                                Placeholder::make('question_header')
                                    ->hiddenLabel()
                                    ->content(function () use ($record): HtmlString {
                                        $name = $record->is_anonymous
                                            ? 'Anonymous'
                                            : e($record->user?->name ?? '-');
                                        $initial = strtoupper(mb_substr($record->is_anonymous ? 'A' : ($record->user?->name ?? '-'), 0, 1));
                                        $date = optional($record->created_at)->format('d M Y');

                                        return new HtmlString('
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div style="width:32px; height:32px; border-radius:9999px; background:#E7D9C6; color:#7A5A3A; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.875rem;">'.$initial.'</div>
                                                    <span style="font-weight:600; color:#111827;">'.$name.'</span>
                                                </div>
                                                <span style="color:#8A8078; font-size:0.875rem;">'.$date.'</span>
                                            </div>
                                        ');
                                    }),

                                Placeholder::make('question_title')
                                    ->hiddenLabel()
                                    ->content(fn (): HtmlString => new HtmlString(
                                        '<div style="font-weight:700; font-size:1rem; color:#111827; margin-top:6px;">'.e($record->title).'</div>'
                                    )),

                                Placeholder::make('question_body')
                                    ->hiddenLabel()
                                    ->content(fn (): HtmlString => new HtmlString(
                                        '<div style="color:#4B5563; line-height:1.6; margin-top:4px;">'.nl2br(e($record->body ?? '')).'</div>'
                                    )),
                            ])
                            ->extraAttributes([
                                'style' => 'background:#F5EFE6; border-radius:12px;',
                            ]),

                        RichEditor::make('body')
                            ->label('Balasan Medis')
                            ->placeholder('Tulis jawaban medis Anda di sini...')
                            ->toolbarButtons(['bold', 'italic', 'bulletList'])
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, ForumQuestion $record): void {
                        $existing = $record->answers()->latest()->first();

                        if ($existing) {
                            $existing->update(['body' => $data['body']]);
                        } else {
                            ForumAnswer::create([
                                'question_id' => $record->forum_question_id,
                                'dokter_id'   => Filament::auth()->id(),
                                'body'        => $data['body'],
                            ]);
                        }

                        Notification::make()
                            ->title('Jawaban berhasil dikirim')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
