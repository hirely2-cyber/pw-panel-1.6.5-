@php
    $icons = [
        'menu'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
        'chev-down' => '<path d="m6 9 6 6 6-6"/>',
        'chev-right'=> '<path d="m9 6 6 6-6 6"/>',
        'chev-left' => '<path d="m15 6-6 6 6 6"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/>',
        'refresh'   => '<path d="M21 12a9 9 0 1 1-3.2-6.9"/><path d="M21 3v6h-6"/>',
        'fullscreen'=> '<path d="M3 9V3h6M21 9V3h-6M3 15v6h6M21 15v6h-6"/>',
        'trash'     => '<path d="M3 6h18M8 6V4h8v2M10 11v6M14 11v6M5 6l1 14h12l1-14"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'accounts'  => '<path d="M16 4h4v16H4V4h4"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M8 10h8M8 14h8M8 18h5"/>',
        'characters'=> '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'server'    => '<rect x="3" y="4" width="18" height="7" rx="1"/><rect x="3" y="13" width="18" height="7" rx="1"/><path d="M7 7.5h.01M7 16.5h.01"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'chat'      => '<path d="M21 12a7 7 0 0 1-7 7H7l-4 3V12a7 7 0 0 1 7-7h4a7 7 0 0 1 7 7z"/>',
        'gm'        => '<path d="M12 2 4 5v6c0 5 3.5 9 8 11 4.5-2 8-6 8-11V5z"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a2 2 0 0 0 .4 2.2l.1.1a2.4 2.4 0 1 1-3.4 3.4l-.1-.1a2 2 0 0 0-2.2-.4 2 2 0 0 0-1.2 1.8V22a2.4 2.4 0 0 1-4.8 0v-.1a2 2 0 0 0-1.3-1.8 2 2 0 0 0-2.2.4l-.1.1a2.4 2.4 0 1 1-3.4-3.4l.1-.1a2 2 0 0 0 .4-2.2 2 2 0 0 0-1.8-1.2H2a2.4 2.4 0 0 1 0-4.8h.1A2 2 0 0 0 3.9 7.5a2 2 0 0 0-.4-2.2l-.1-.1a2.4 2.4 0 1 1 3.4-3.4l.1.1a2 2 0 0 0 2.2.4H9a2 2 0 0 0 1.2-1.8V0"/>',
        'log'       => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'key'       => '<circle cx="7" cy="14" r="4"/><path d="m10 11 10-10M16 5l3 3M14 7l3 3"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    ];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $size ?? 16 }}" height="{{ $size ?? 16 }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="{{ $class ?? '' }}">
    {!! $icons[$name] ?? '' !!}
</svg>
