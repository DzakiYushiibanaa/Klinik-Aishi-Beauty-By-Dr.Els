<?php

namespace App\Filament\Resources\RekamMedis\Tables\Concerns;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

trait HasRekamMedisFilters
{
    private static function filters(): array
    {
        return [
            SelectFilter::make('jenis')
                ->label('Jenis Kunjungan')
                ->options([
                    'konsultasi' => 'Consultation',
                    'treatment'  => 'Treatment',
                    'follow_up'  => 'Follow-up',
                ])
                ->query(function (Builder $query, array $data) {
                    if (filled($data['value'] ?? null)) {
                        $query->whereHas('appointment', fn ($q) => $q->where('jenis', $data['value']));
                    }
                }),
        ];
    }

    // ═══════════════════════════════════════════════════════
    // ROW ACTIONS
    // ═══════════════════════════════════════════════════════

}
