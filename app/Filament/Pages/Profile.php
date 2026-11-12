<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $title = 'Profil Ayarları';
    protected static ?string $navigationLabel = 'Profil Ayarları';
    protected static ?string $slug = 'profil';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.profile';

    public $name;
    public $email;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(1)->schema([
                Forms\Components\Section::make('Kullanıcı Bilgileri')
                    ->description('Adınızı ve panel girişinde kullandığınız e-posta adresinizi güncelleyebilirsiniz.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Ad Soyad')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('E-Posta Adresi')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', ignorable: auth()->user()),
                    ])->columns(2),

                Forms\Components\Section::make('Şifre Değiştir')
                    ->description('Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakabilirsiniz.')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Mevcut Şifreniz')
                            ->password()
                            ->requiredWith('new_password')
                            ->currentPassword(),
                        Forms\Components\TextInput::make('new_password')
                            ->label('Yeni Şifre')
                            ->password()
                            ->rules(['nullable', 'min:8', 'confirmed']),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Yeni Şifre Tekrarı')
                            ->password()
                            ->requiredWith('new_password'),
                    ])->columns(3),
            ]),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['new_password'])) {
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => null,
            'new_password' => null,
            'new_password_confirmation' => null,
        ]);

        Notification::make()
            ->title('Profil güncellendi')
            ->body('Profil ve şifre bilgileriniz başarıyla kaydedildi.')
            ->success()
            ->send();
    }
}
