<?php

namespace App\Filament\Pages;

use App\Models\Dokter;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

class EditProfilDokter extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.edit-profil-dokter';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user-circle';
    }

    public static function getNavigationLabel(): string
    {
        return 'Profil Saya';
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Edit Profil Dokter';
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'profil-dokter';
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = auth()->user();
        return $user && ($user->hasRole('dokter') || $user->hasRole('super_admin') || $user->hasRole('admin'));
    }

    public ?array $data = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $dokter = $user->dokterProfile;

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'pengalaman_tahun' => $dokter?->pengalaman_tahun,
            'status_praktik' => $dokter?->status_praktik ?? 'Aktif',
            'foto_profil_path' => $dokter?->foto_profil_path,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Akun')
                    ->description('Data login dan kontak pribadi.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                            
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                            
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(255),
                            
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(false)
                            ->helperText('Kosongkan jika tidak ingin mengubah password.'),
                    ])->columns(2),

                Section::make('Informasi Dokter')
                    ->description('Data detail mengenai keahlian dan status dokter.')
                    ->schema([
                        TextInput::make('pengalaman_tahun')
                            ->label('Pengalaman (Tahun)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                            
                        Select::make('status_praktik')
                            ->label('Status Praktik')
                            ->options([
                                'Aktif' => 'Aktif',
                                'Cuti' => 'Cuti',
                                'Nonaktif' => 'Nonaktif',
                            ])
                            ->required(),
                            
                        FileUpload::make('foto_profil_path')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('dokter-profiles')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var User $user */
        $user = auth()->user();

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ];

        if (filled($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $user->update($userData);

        if ($user->dokterProfile) {
            $user->dokterProfile->update([
                'pengalaman_tahun' => $data['pengalaman_tahun'],
                'status_praktik' => $data['status_praktik'],
                'foto_profil_path' => $data['foto_profil_path'],
            ]);
        }

        Notification::make()
            ->success()
            ->title('Profil berhasil diperbarui')
            ->send();
    }
}
