<div style="display: flex; flex-direction: column; gap: 12px; max-height: 480px; overflow-y: auto; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
    @forelse ($messages as $message)
        @php
            $isAdmin = $message->author_type === 'admin';
        @endphp
        <div style="max-width: 80%; display: flex; flex-direction: column; {{ $isAdmin ? 'margin-left: auto; align-items: flex-end;' : 'margin-right: auto; align-items: flex-start;' }}">
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px; font-size: 11px; font-weight: 600; color: #64748b;">
                <span>{{ $isAdmin ? '👑 Destek Ekibi' : '👤 Müşteri' }}</span>
                <span>·</span>
                <span>{{ $message->created_at ? $message->created_at->format('d.m.Y H:i') : '' }}</span>
            </div>
            <div style="padding: 12px 16px; border-radius: {{ $isAdmin ? '14px 14px 2px 14px' : '14px 14px 14px 2px' }}; background: {{ $isAdmin ? '#28231f' : '#ffffff' }}; color: {{ $isAdmin ? '#ffffff' : '#1e293b' }}; border: {{ $isAdmin ? 'none' : '1px solid #e2e8f0' }}; box-shadow: 0 2px 4px rgba(0,0,0,0.03); font-size: 13.5px; line-height: 1.55; white-space: pre-wrap; overflow-wrap: anywhere;">{{ $message->body }}</div>
        </div>
    @empty
        <div style="text-align: center; padding: 24px; color: #94a3b8; font-size: 13px;">
            Henüz herhangi bir mesaj bulunmuyor.
        </div>
    @endforelse
</div>
