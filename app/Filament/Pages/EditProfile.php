<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.edit-profile';

    protected static bool $shouldRegisterNavigation = false;

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
        return 'Profil Saya';
    }

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Profil Saya';
    }

    public function getSubheading(): ?string
    {
        return 'Perbarui informasi akun dan data pribadi Anda.';
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'profile';
    }

    public ?array $data = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->form->fill([
            'name'           => $user->name,
            'email'          => $user->email,
            'phone'          => $user->phone,
            'tanggal_lahir'  => $user->tanggal_lahir,
            'jenis_kelamin'  => $user->jenis_kelamin,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Akun')
                    ->description('Data login dan identitas pribadi yang digunakan di dalam sistem.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Email tidak dapat diubah.'),

                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(false)
                            ->helperText('Kosongkan jika tidak ingin mengubah password.'),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->same('password')
                            ->visible(fn (callable $get) => filled($get('password'))),
                    ]),

                Section::make('Data Pribadi')
                    ->description('Informasi tambahan untuk kelengkapan profil.')
                    ->columns(2)
                    ->footerActions([
                        \Filament\Actions\Action::make('cancel')
                            ->label('Batal')
                            ->color('gray')
                            ->url(fn () => url()->previous()),

                        \Filament\Actions\Action::make('save')
                            ->label('Simpan Perubahan')
                            ->color('primary')
                            ->action('save'),
                    ])
                    ->footerActionsAlignment(\Filament\Support\Enums\Alignment::End)
                    ->schema([
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+62 8xx xxxx xxxx'),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->maxDate(now()),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->native(false)
                            ->placeholder('Pilih jenis kelamin'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var User $user */
        $user = auth()->user();

        $payload = [
            'name'          => $data['name'],
            'phone'         => $data['phone'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        // Reset password field so the input is empty after save.
        $this->data['password'] = null;
        $this->data['password_confirmation'] = null;

        Notification::make()
            ->success()
            ->title('Profil berhasil diperbarui')
            ->body('Informasi akun Anda telah disimpan.')
            ->send();
    }
}
