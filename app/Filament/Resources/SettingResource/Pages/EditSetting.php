<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected static ?string $title = 'Genel Ayarlar';

    protected function getActions(): array
    {
        return [
            Actions\Action::make('test_mail')
                ->label('Test Maili Gönder')
                ->icon('heroicon-o-mail')
                ->color('secondary')
                ->requiresConfirmation()
                ->modalHeading('Test E-postası Gönder')
                ->modalSubheading('Kaydedilen ayarlarla bir test e-postası göndermek üzeresiniz. Lütfen test maili için alıcı e-posta adresini girin.')
                ->form([
                    \Filament\Forms\Components\TextInput::make('email')
                        ->label('Alıcı E-Posta Adresi')
                        ->email()
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $setting = \App\Models\Setting::first();
                        if (!$setting || empty($setting->mail_settings)) {
                            throw new \Exception('Mail ayarları bulunamadı. Lütfen önce ayarları kaydedin.');
                        }
                        
                        config([
                            'mail.mailers.smtp.host' => $setting->mail_settings['host'] ?? '',
                            'mail.mailers.smtp.port' => $setting->mail_settings['port'] ?? '',
                            'mail.mailers.smtp.encryption' => $setting->mail_settings['encryption'] ?? '',
                            'mail.mailers.smtp.username' => $setting->mail_settings['username'] ?? '',
                            'mail.mailers.smtp.password' => $setting->mail_settings['password'] ?? '',
                            'mail.from.address' => $setting->mail_settings['from_address'] ?? '',
                            'mail.from.name' => $setting->mail_settings['from_name'] ?? '',
                        ]);

                        \Illuminate\Support\Facades\Mail::raw('Bu bir test mesajıdır. Mail ayarlarınızın başarıyla çalıştığını gösterir.', function ($message) use ($data) {
                            $message->to($data['email'])
                                    ->subject('Test E-postası - Simge VIP');
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Başarılı')
                            ->body('Test maili gönderildi.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Hata')
                            ->body('Mail gönderilemedi: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Site ayarları başarıyla kaydedildi.';
    }
}
