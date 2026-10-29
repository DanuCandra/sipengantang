@props(['area', 'userName'])

@php($dashboardRoute = $sidebar['dashboardRoute'])

<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='description' content='Dashboard GantangScore untuk operasional penilaian lomba.'>
        <title>{{ $area }} — {{ config('app.name', 'GantangScore') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <noscript><style>[data-sidebar] { position: static; width: auto; transform: none; } [data-sidebar-toggle], [data-sidebar-close] { display: none; }</style></noscript>
    </head>
    <body class='min-h-screen bg-canvas antialiased'>
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-white focus:p-4 focus:font-bold focus:text-forest-900">Lewati ke konten utama</a>
        <div data-sidebar-backdrop class='fixed inset-0 z-30 hidden bg-stone-950/35 backdrop-blur-sm lg:hidden'></div>

        <aside id="dashboard-navigation" data-sidebar aria-label="Navigasi dashboard" class='fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col overflow-y-auto border-r border-white/10 bg-forest-900 px-5 py-6 text-white transition-transform duration-200 motion-reduce:transition-none lg:translate-x-0'>
            <button data-sidebar-close type="button" aria-label="Tutup navigasi" class="mb-4 inline-flex min-h-11 shrink-0 items-center self-end rounded-lg border border-white/25 px-4 text-sm font-bold lg:hidden">Tutup ×</button>
            <a href='{{ route($dashboardRoute) }}' class='w-fit shrink-0 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white'>
                <x-application-logo class='[&>span:last-child>span]:text-white [&>span:last-child>span:last-child]:text-forest-200' />
            </a>

            <div class='mt-6 shrink-0 rounded-2xl border border-white/10 bg-white/[0.07] p-3'>
                <div class='flex items-center gap-3'>
                    <div class='grid size-10 place-items-center rounded-xl bg-gold-400 font-extrabold text-forest-900'>
                        {{ str($userName)->substr(0, 1)->upper() }}
                    </div>
                    <div class='min-w-0'>
                        <p class='truncate text-sm font-bold'>{{ $userName }}</p>
                        <p class='mt-1 text-xs font-semibold text-forest-200'>Area {{ $area }}</p>
                    </div>
                </div>
            </div>

            @if ($area === 'Admin')
                <form method="GET" action="{{ route('admin.workspace', ['section' => $sidebar['section']]) }}" class="mt-5 shrink-0 rounded-xl border border-white/15 bg-white/5 p-3">
                    <label for="sidebar-event" class="mb-2 block text-xs font-bold uppercase tracking-wider text-forest-200">Event aktif</label>
                    <select id="sidebar-event" name="event_id" @disabled($sidebar['events']->isEmpty()) class="min-h-11 w-full min-w-0 rounded-lg border border-white/25 bg-forest-800 px-2 text-sm text-white focus-visible:outline-2 focus-visible:outline-gold-300">
                        @forelse ($sidebar['events'] as $option)<option value="{{ $option->id }}" @selected($sidebar['selected']?->id === $option->id)>{{ $option->name }}</option>@empty<option>Belum ada event ditugaskan</option>@endforelse
                    </select>
                    <button type="submit" @disabled($sidebar['events']->isEmpty()) class="mt-2 min-h-10 w-full rounded-lg bg-gold-300 px-3 text-xs font-extrabold text-forest-900 hover:bg-gold-200 disabled:opacity-50">Buka event pilihan</button>
                </form>
            @endif
            <nav aria-label='Navigasi utama' class='mb-6 mt-5 shrink-0 space-y-1'>
                @foreach ($sidebar['links'] as $link)
                    <x-sidebar-link :link="$link" />
                @endforeach

            </nav>

            <div class='mt-auto shrink-0 border-t border-white/10 pt-5'>
                <form method='POST' action='{{ route('logout') }}'>
                    @csrf
                    <button type='submit' class='flex min-h-11 w-full items-center gap-3 rounded-xl px-3.5 text-sm font-bold text-forest-100 transition hover:bg-white/10 hover:text-white'>
                        <svg aria-hidden='true' class='size-5' viewBox='0 0 24 24' fill='none'>
                            <path d='M10 5H5v14h5M14 8l4 4-4 4M18 12H9' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div data-dashboard-content class='min-h-screen min-w-0 lg:pl-72'>
            <header class='sticky top-0 z-20 border-b border-stone-200/80 bg-canvas/90 backdrop-blur-xl'>
                <div class='flex h-20 items-center justify-between px-5 sm:px-8 lg:px-10'>
                    <div class='flex items-center gap-3'>
                        <button data-sidebar-toggle type='button' aria-controls="dashboard-navigation" aria-expanded='false' aria-label='Buka navigasi' class='grid size-11 place-items-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm lg:hidden'>
                            <svg aria-hidden='true' class='size-5' viewBox='0 0 24 24' fill='none'>
                                <path d='M4 7h16M4 12h16M4 17h16' stroke='currentColor' stroke-width='2' stroke-linecap='round'/>
                            </svg>
                        </button>
                        <div>
                            <p class='text-xs font-bold uppercase tracking-[0.18em] text-forest-600'>GantangScore</p>
                            <p class='mt-1 text-sm font-bold text-stone-700'>Dashboard {{ $area }}</p>
                        </div>
                    </div>

                    <div class='hidden items-center gap-2 rounded-full border border-forest-200 bg-white px-3 py-2 sm:flex'>
                        <span class='size-2 rounded-full bg-forest-500'></span>
                        <span class='text-xs font-bold text-forest-800'>Akun aktif</span>
                    </div>
                </div>
            </header>

            <main id="main-content" tabindex="-1" class='min-w-0 px-5 py-8 sm:px-8 lg:px-10 lg:py-10'>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
