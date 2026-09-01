<div style="padding:18px 20px; background:linear-gradient(135deg,#FAF8F5 0%,#FFFFFF 100%); border:1px solid #EDE8E3; border-radius:14px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div style="min-width:0;">
            <div style="font-size:1.15rem; font-weight:700; color:#1A1A1A; line-height:1.3;">{{ $nama }}</div>
            @if($rm)
            <div style="font-size:0.85rem; color:#8A8078; margin-top:4px; font-variant-numeric:tabular-nums;">{{ $rm }}</div>
            @endif
            <div style="display:flex; gap:14px; align-items:center; margin-top:10px; flex-wrap:wrap;">
                <span style="display:inline-flex; align-items:center; gap:6px; font-size:0.875rem; color:#1A1A1A; font-weight:500;">
                    <svg width="16" height="16" fill="none" stroke="#B8956F" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    {{ $jenisLabel }}
                </span>
                <span style="font-size:0.875rem; color:#8A8078;">·</span>
                <span style="display:inline-flex; align-items:center; gap:6px; font-size:0.875rem; color:#1A1A1A;">
                    <svg width="16" height="16" fill="none" stroke="#B8956F" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $tanggal }} • {{ $jam }} WIB
                </span>
            </div>
        </div>
        <span style="display:inline-block; padding:5px 12px; background:{{ $statusBg }}; color:{{ $statusColor }}; font-size:0.75rem; font-weight:600; border-radius:9999px; flex-shrink:0;">
            {{ $statusLabel }}
        </span>
    </div>
</div>
