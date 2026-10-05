<?php

namespace App\Http\Controllers;

use App\Services\GithubDeployService;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Throwable;

class GithubDeployController extends Controller
{
    public function store(GithubDeployService $deploy): RedirectResponse
    {
        abort_unless(app()->environment('local'), 404);

        if (! $deploy->isEnabled()) {
            try {
                $preview = $deploy->preview();
                $body = 'Paket önizlemesi hazır. Canlıya gönderim kapalı. '
                    . $preview['message'];
                $body .= ' Gönderimi açmak için ' . app()->environmentFile()
                    . ' dosyasında DEPLOY_ENABLED=true ayarlayın.';
                if ($preview['files'] !== []) {
                    $body .= ' Örnek: ' . collect($preview['files'])->take(6)->implode(', ')
                        . (count($preview['files']) > 6 ? ' …' : '');
                }
                $notification = Notification::make()
                    ->title(($preview['issues'] ?? []) === []
                        ? 'Canlı aktarım beklemede'
                        : 'Canlı paket kontrol uyarısı')
                    ->body($body);

                if (($preview['issues'] ?? []) === []) {
                    $notification->warning()->send();
                } else {
                    $notification->danger()->send();
                }
            } catch (Throwable $exception) {
                Notification::make()
                    ->title('Önizleme alınamadı')
                    ->body($exception->getMessage())
                    ->danger()
                    ->send();
            }

            return back();
        }

        try {
            $message = $deploy->run();
            Notification::make()
                ->title('Canlıya aktarıldı')
                ->body($message)
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Aktarım olmadı')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }

        return back();
    }
}
