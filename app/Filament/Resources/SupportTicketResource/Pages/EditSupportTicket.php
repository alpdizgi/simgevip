<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Filament\Resources\SupportTicketResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;
    protected ?string $replyText = null;

    public function mount($record): void
    {
        parent::mount($record);
        $this->record->update(['admin_read_at' => now()]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->replyText = trim((string) ($data['reply'] ?? ''));
        unset($data['reply']);
        if ($this->replyText !== '') {
            $data['status'] = 'waiting_customer';
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::transaction(function () use ($record, $data) {
            $record->update($data);
            if ($this->replyText !== null && $this->replyText !== '') {
                $record->messages()->create([
                    'author_type' => 'admin',
                    'author_id' => Auth::id(),
                    'body' => $this->replyText,
                ]);
                $record->update(['last_reply_at' => now(), 'customer_read_at' => null]);
            }
        });
        return $record;
    }

    protected function afterSave(): void
    {
        $this->replyText = null;
        $this->data['reply'] = null;
    }
}
