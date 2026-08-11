<?php

namespace App\Filament\Resources\Appointments\Schemas\Sections;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;

class IdentitasPasienSection
{
    public static function make(): Section
    {
        return // ═══════════════════════════════════════════
                // Section 1: Identitas Pasien
                // ═══════════════════════════════════════════
                Section::make('Identitas Pasien')
                    ->schema([
                        Select::make('user_id')
                            ->label('Pasien')
                            ->relationship(
                                name: 'pasien',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) =>
                                $query->whereHas('roles', fn(Builder $q) => $q->where('name', 'pasien'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn() => (Filament::auth()->user()?->hasRole('pasien') ?? false))
                            ->default(fn() => (Filament::auth()->user()?->hasRole('pasien') ?? false)
                                ? Filament::auth()->id()
                                : null)
                            ->createOptionForm([
                                TextInput::make('name')->required()->label('Nama Lengkap'),
                                TextInput::make('email')->email()->label('Email'),
                                TextInput::make('phone')->tel()->label('Nomor Telepon (Opsional)'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $user = \App\Models\User::create([
                                    'name' => $data['name'],
                                    'email' => $data['email'] ?? null,
                                    'phone' => $data['phone'] ?? null,
                                    'password' => bcrypt('password123'),
                                ]);
                                $user->assignRole('pasien');
                                return $user->id;
                            })
                            ->columnSpanFull(),
                    ]);
    }
}
