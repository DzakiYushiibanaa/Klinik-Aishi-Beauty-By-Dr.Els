<?php

namespace App\Filament\Resources\PatientForms\Tables;

use App\Filament\Resources\PatientForms\Schemas\ReviewPatientFormForm;
use App\Models\PatientForm;
use App\Services\PraKonsul\ReviewSubmissionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientFormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['pasien.user', 'dokter', 'reviewer']))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                TextColumn::make('patient_form_id')->label('ID')->sortable(),

                TextColumn::make('pasien_name')
                    ->label('Pasien')
                    ->weight('medium')
                    ->getStateUsing(fn (PatientForm $record): string =>
                        $record->pasien?->user?->name
                            ?: ($record->pasien?->nama ?: '—')
                    )
                    ->description(fn (PatientForm $record): ?string =>
                        $record->pasien?->no_rekam_medis
                    )
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('pasien', function (Builder $q) use ($search) {
                            $q->where('nama', 'like', "%{$search}%")
                              ->orWhere('no_rekam_medis', 'like', "%{$search}%")
                              ->orWhereHas('user', fn (Builder $u) =>
                                  $u->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                              );
                        });
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baru', 'submitted', 'menunggu' => 'warning',
                        'ditinjau', 'in_review', 'reviewed' => 'info',
                        'direkomendasikan' => 'success',
                        'selesai' => 'gray',
                        'ditutup' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'baru', 'submitted', 'menunggu' => 'Menunggu Review',
                        'ditinjau', 'in_review', 'reviewed' => 'Sedang Direview',
                        'direkomendasikan' => 'Direkomendasikan',
                        'selesai' => 'Selesai',
                        'ditutup' => 'Ditutup',
                        default => ucfirst($state),
                    })
                    ->sortable(),
                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('reviewed_at')
                    ->label('Direview')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')->label('Dibuat')
                    ->date('d M Y')
                    ->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                self::actionMulaiReview(),       // Konfirmasi (status: baru)
                self::actionLanjutkanReview(),   // Slide-over panel (status: ditinjau / direkomendasikan)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function actionMulaiReview(): Action
    {
        return Action::make('mulai_review')
            ->label('Mulai Review')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('primary')
            ->visible(fn (PatientForm $record): bool =>
                in_array($record->status, ['baru', 'submitted', 'menunggu'], true)
                && (Filament::auth()->user()?->hasAnyRole(['dokter', 'admin', 'super_admin']) ?? false)
            )
            ->requiresConfirmation()
            ->modalHeading('Mulai Review Pra-Konsultasi?')
            ->modalDescription('Form pasien akan ditandai sebagai sedang direview oleh Anda.')
            ->modalSubmitActionLabel('Mulai Review')
            ->modalCancelActionLabel('Batal')
            ->modalWidth('sm')
            ->modalFooterActionsAlignment(Alignment::End)
            ->modalIcon('heroicon-o-clipboard-document-check')
            ->modalIconColor('primary')
            ->action(function (PatientForm $record): void {
                $userId = Filament::auth()->id();
                if (! $userId) return;

                try {
                    app(ReviewSubmissionService::class)->start($record, (int) $userId);

                    // Kirim WA ke pasien (best-effort).
                    $fresh = PatientForm::with(['pasien.user', 'reviewer'])
                        ->find($record->patient_form_id);
                    if ($fresh) {
                        app(\App\Services\FonnteService::class)->sendPraKonsulReviewStarted($fresh);
                    }

                    Notification::make()
                        ->title('Review dimulai')
                        ->body('Pasien sudah dinotifikasi via WhatsApp. Klik "Lanjutkan Review" untuk membuka panel.')
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Mulai review gagal: '.$e->getMessage());
                    Notification::make()
                        ->title('Gagal memulai review')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
    private static function actionLanjutkanReview(): Action
    {
        return Action::make('lanjutkan_review')
            ->label(fn (PatientForm $record) => $record->status === 'direkomendasikan'
                ? 'Lihat Rekomendasi'
                : 'Lanjutkan Review'
            )
            ->icon(fn (PatientForm $record) => $record->status === 'direkomendasikan' ? 'heroicon-o-eye' : 'heroicon-o-pencil-square')
            ->color(fn (PatientForm $record) => $record->status === 'direkomendasikan' ? 'gray' : 'info')
            ->visible(fn (PatientForm $record): bool =>
                in_array($record->status, ['ditinjau', 'in_review', 'reviewed', 'direkomendasikan'], true)
                && (Filament::auth()->user()?->hasAnyRole(['dokter', 'admin', 'super_admin']) ?? false)
            )
            ->slideOver()
            ->modalWidth('7xl')
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalHeading(fn (PatientForm $record): string =>
                'Review Pra-Konsultasi · '.($record->pasien?->user?->name ?: ($record->pasien?->nama ?: 'Pasien'))
            )
            ->modalDescription(fn (PatientForm $record): string =>
                'No. RM '.($record->pasien?->no_rekam_medis ?: '—')
                .' · diisi '.optional($record->created_at)->translatedFormat('d M Y, H:i')
            )
            ->fillForm(fn (PatientForm $record): array =>
                ReviewPatientFormForm::defaults($record->fresh())
            )
            ->schema(fn (PatientForm $record) => ReviewPatientFormForm::schema($record->fresh()))
            ->modalSubmitAction(function (\Filament\Actions\Action $action, PatientForm $record) {
                if ($record->status === 'direkomendasikan') {
                    return false;
                }
                return $action->label('Kirim Rekomendasi')->extraAttributes([
                    'wire:confirm' => 'Kirim Rekomendasi?

Rekomendasi yang dikirim akan langsung terlihat oleh pasien.
Pastikan analisa dan rekomendasi treatment / skincare sudah sesuai.',
                ]);
            })
            ->modalIcon(fn(PatientForm $record) => $record->status === 'direkomendasikan' ? null : 'heroicon-o-paper-airplane')
            ->modalIconColor('primary')
            ->modalAlignment(Alignment::Start)
            ->modalCancelActionLabel(fn (PatientForm $record) => $record->status === 'direkomendasikan' ? 'Tutup' : 'Cancel')
            ->action(function (array $data, PatientForm $record): void {
                try {
                    app(ReviewSubmissionService::class)->submit($record, $data);

                    Notification::make()
                        ->title('Rekomendasi terkirim')
                        ->body('Pasien akan menerima notifikasi rekomendasi treatment & skincare.')
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Submit pra-konsul review gagal', [
                        'patient_form_id' => $record->patient_form_id,
                        'error'           => $e->getMessage(),
                    ]);

                    Notification::make()
                        ->title('Gagal menyimpan rekomendasi')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
