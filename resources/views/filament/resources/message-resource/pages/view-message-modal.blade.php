<div class="space-y-4 text-left outline-none focus:outline-none" tabindex="0" autofocus>
    <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Gönderen Kişi</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">{{ $record->sender_name }}</div>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">E-posta Adresi</div>
                <div class="text-sm font-medium text-primary-600 dark:text-primary-400 mt-1">
                    <a href="mailto:{{ $record->email }}?subject={{ urlencode('Re: ' . ($record->subject ?? '')) }}" class="hover:underline">{{ $record->email }}</a>
                </div>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Konu</div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-1">{{ $record->subject ?? 'Konu Belirtilmemiş' }}</div>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tarih</div>
                <div class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $record->created_at ? $record->created_at->format('d.m.Y H:i') : '—' }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
        <div class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 border-b border-gray-100 dark:border-gray-800 pb-2">Mesaj İçeriği</div>
        <div class="text-sm leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{!! nl2br(e($record->message)) !!}</div>
    </div>
</div>
