@props(['link'])
<a href="{{ $link['url'] }}" @if ($link['active']) aria-current="page" @endif @class(['flex min-h-11 items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-300', 'bg-white text-forest-900 shadow-sm' => $link['active'], 'text-forest-100 hover:bg-white/10 hover:text-white' => ! $link['active']])>
    <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        @switch($link['icon'])
            @case('home')<path d="m3 10 9-7 9 7v11h-6v-7H9v7H3z" />@break
            @case('event')@case('events')<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18"/>@break
            @case('participants')@case('judges')@case('profile')<circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2m2-16a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 5"/>@break
            @case('payments')<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18m-13 5h3"/>@break
            @case('results')@case('prizes')@case('certificates')<path d="M8 3h8v6a4 4 0 0 1-8 0V3Zm0 2H4v3a4 4 0 0 0 4 4m8-7h4v3a4 4 0 0 1-4 4m-4 1v7m-4 1h8"/>@break
            @case('judging')<path d="M4 20V4m0 16h16M8 16v-4m5 4V8m5 8V5"/>@break
            @case('violations')<path d="m12 3 10 18H2L12 3Zm0 6v5m0 3h.01"/>@break
            @case('settings')<path d="M4 7h16M4 17h16"/><circle cx="9" cy="7" r="3"/><circle cx="15" cy="17" r="3"/>@break
            @case('add')<path d="M12 4v16M4 12h16"/>@break
            @case('birds')<path d="M4 18c4-9 10-12 16-11-1 7-6 12-13 12m0 0-3 3m3-3 7-7"/>@break
            @default<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M8 8h8m-8 4h8m-8 4h5"/>
        @endswitch
    </svg>
    <span>{{ $link['label'] }}</span>
</a>
