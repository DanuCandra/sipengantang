@props(['area', 'userName'])

<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='description' content='Dashboard GantangScore untuk operasional penilaian lomba.'>
        <title>{{ $area }} — {{ config('app.name', 'GantangScore') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class='min-h-screen bg-canvas antialiased'>
        <div data-sidebar-backdrop class='fixed inset-0 z-30 hidden bg-stone-950/35 backdrop-blur-sm lg:hidden'></div>

        <aside data-sidebar class='fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-white/10 bg-forest-900 px-5 py-6 text-white transition-transform duration-200 lg:translate-x-0'>
            <a href='{{ route('dashboard') }}' class='w-fit rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white'>
                <x-application-logo class='[&>span:last-child>span]:text-white [&>span:last-child>span:last-child]:text-forest-200' />
            </a>

            <div class='mt-9 rounded-2xl border border-white/10 bg-white/[0.07] p-4'>
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

            <nav aria-label='Navigasi utama' class='mt-8 space-y-2'>
                <a href='{{ route('dashboard') }}' class='flex min-h-11 items-center gap-3 rounded-xl bg-white px-3.5 text-sm font-bold text-forest-900 shadow-sm'>
                    <svg aria-hidden='true' class='size-5' viewBox='0 0 24 24' fill='none'>
                        <path d='M4 10.5 12 4l8 6.5V20h-5v-6H9v6H4v-9.5Z' stroke='currentColor' stroke-width='1.8' stroke-linejoin='round'/>
                    </svg>
                    Beranda
                </a>

                @foreach ([
                    ['Lomba', 'M12 3 4 7v5c0 4.7 3.4 7.7 8 9 4.6-1.3 8-4.3 8-9V7l-8-4Z'],
                    ['Penilaian', 'M6 3h12v18H6zM9 8h6M9 12h6M9 16h4'],
                    ['Akun', 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0'],
                ] as [$label, $path])
                    <span class='group flex min-h-11 cursor-not-allowed items-center gap-3 rounded-xl px-3.5 text-sm font-semibold text-forest-200/70'>
                        <svg aria-hidden='true' class='size-5' viewBox='0 0 24 24' fill='none'>
                            <path d='{{ $path }}' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                        {{ $label }}
                        <span class='ml-auto rounded-md bg-white/10 px-1.5 py-1 text-[9px] font-bold uppercase tracking-wider'>Segera</span>
                    </span>
                @endforeach
            </nav>

            <div class='mt-auto border-t border-white/10 pt-5'>
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

        <div class='min-h-screen lg:pl-72'>
            <header class='sticky top-0 z-20 border-b border-stone-200/80 bg-canvas/90 backdrop-blur-xl'>
                <div class='flex h-20 items-center justify-between px-5 sm:px-8 lg:px-10'>
                    <div class='flex items-center gap-3'>
                        <button data-sidebar-toggle type='button' aria-expanded='false' aria-label='Buka navigasi' class='grid size-11 place-items-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm lg:hidden'>
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

            <main class='px-5 py-8 sm:px-8 lg:px-10 lg:py-10'>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
