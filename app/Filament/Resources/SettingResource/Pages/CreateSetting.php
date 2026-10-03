<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected static ?string $title = 'Genel Ayarlar';

    protected static bool $canCreateAnother = false;

    protected ?string $subheading = 'Marka, iletişim ve sosyal medya bilgilerinizi tek yerden yönetin.';

    public function mount(): void
    {
        parent::mount();

        $setting = Setting::first();
        if ($setting) {
            $this->redirect(SettingResource::getUrl('edit', ['record' => $setting]));
            return;
        }
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()->label('Ayarları Kaydet');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()->url(route('filament.pages.dashboard'));
    }

    protected function getRedirectUrl(): string
    {
        return SettingResource::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Site ayarları başarıyla kaydedildi.';
    }
}
