<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='description' content='GantangScore adalah sistem penilaian gantangan untuk penyelenggara, juri, dan peserta lomba burung.'>
        <title>{{ config('app.name', 'GantangScore') }} — Sistem Penilaian Gantangan</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class='bg-canvas antialiased'>
        <header class='relative z-20 border-b border-stone-200/70 bg-canvas/90 backdrop-blur-xl'>
            <nav aria-label='Navigasi utama' class='mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-8'>
                <a href='{{ route('home') }}' class='rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4'>
                    <x-application-logo class='[&>span:last-child]:hidden sm:[&>span:last-child]:block' />
                </a>

                <div class='flex items-center gap-2'>
                    @auth
                        <x-ui.link-button route='dashboard'>Buka dashboard</x-ui.link-button>
                    @else
                        <span class='hidden sm:block'>
                            <x-ui.link-button route='login' variant='ghost'>Masuk</x-ui.link-button>
                        </span>
                        <x-ui.link-button route='register'>
                            <span class='sm:hidden'>Daftar</span>
                            <span class='hidden sm:inline'>Daftar peserta</span>
                        </x-ui.link-button>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            <section class='relative overflow-hidden pb-20 pt-14 sm:pt-20 lg:pb-28 lg:pt-24'>
                <div class='absolute -left-32 top-16 size-96 rounded-full bg-forest-200/35 blur-3xl'></div>
                <div class='absolute -right-32 top-0 size-96 rounded-full bg-gold-100/70 blur-3xl'></div>

                <div class='relative mx-auto grid max-w-7xl items-center gap-14 px-5 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(28rem,0.88fr)]'>
                    <div>
                        <div class='inline-flex items-center gap-2 rounded-full border border-forest-200 bg-white/80 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-forest-700 shadow-sm'>
                            <span class='size-2 rounded-full bg-gold-400'></span>
                            Sistem Penilaian Gantangan
                        </div>

                        <h1 class='mt-7 max-w-3xl text-4xl font-extrabold leading-[1.08] tracking-tight text-stone-900 sm:text-5xl lg:text-6xl'>
                            Penilaian lomba yang
                            <span class='text-forest-600'>rapi, cepat,</span>
                            dan dapat dipercaya.
                        </h1>
                        <p class='mt-6 max-w-2xl text-base leading-8 text-stone-600 sm:text-lg'>
                            GantangScore menyatukan penyelenggara, juri, dan peserta dalam alur kerja yang jelas—mulai dari identitas pengguna hingga hasil penilaian.
                        </p>

                        <div class='mt-9 flex flex-col gap-3 sm:flex-row'>
                            @auth
                                <x-ui.link-button route='dashboard' class='sm:px-7'>Masuk ke ruang kerja</x-ui.link-button>
                            @else
                                <x-ui.link-button route='register' class='sm:px-7'>Mulai sebagai peserta</x-ui.link-button>
                                <x-ui.link-button route='login' variant='secondary' class='sm:px-7'>Saya sudah punya akun</x-ui.link-button>
                            @endauth
                        </div>

                        <div class='mt-9 flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold text-stone-600'>
                            @foreach (['Akses sesuai peran', 'Data terstruktur', 'Alur bertahap'] as $item)
                                <span class='inline-flex items-center gap-2'>
                                    <svg aria-hidden='true' class='size-4 text-forest-600' viewBox='0 0 20 20' fill='none'>
                                        <path d='m4 10 3.5 3.5L16 5' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
                                    </svg>
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class='relative mx-auto w-full max-w-xl'>
                        <div class='absolute -inset-6 rounded-[2.5rem] bg-forest-200/40 blur-2xl'></div>
                        <div class='relative overflow-hidden rounded-3xl border border-white bg-white p-4 shadow-soft sm:p-6'>
                            <div class='flex items-center justify-between border-b border-stone-100 pb-5'>
                                <div class='flex items-center gap-3'>
                                    <x-application-logo compact />
                                    <div>
                                        <p class='text-xs font-bold uppercase tracking-[0.16em] text-forest-600'>Ruang kerja lomba</p>
                                        <p class='mt-1 text-sm font-extrabold text-stone-900'>Ringkasan operasional</p>
                                    </div>
                                </div>
                                <span class='rounded-full bg-forest-50 px-3 py-1.5 text-xs font-extrabold text-forest-700'>Aktif</span>
                            </div>

                            <div class='mt-5 grid grid-cols-2 gap-3'>
                                <div class='rounded-2xl bg-forest-900 p-5 text-white'>
                                    <p class='text-xs font-bold uppercase tracking-wider text-forest-200'>Fondasi akses</p>
                                    <p class='mt-5 text-2xl font-extrabold'>4 peran</p>
                                    <p class='mt-1 text-xs text-forest-200'>Owner · Admin · Juri · Peserta</p>
                                </div>
                                <div class='rounded-2xl bg-gold-50 p-5'>
                                    <p class='text-xs font-bold uppercase tracking-wider text-gold-600'>Status sistem</p>
                                    <div class='mt-5 flex items-center gap-2'>
                                        <span class='size-3 rounded-full bg-gold-400 ring-4 ring-gold-200'></span>
                                        <p class='text-lg font-extrabold text-stone-900'>Siap tumbuh</p>
                                    </div>
                                    <p class='mt-1 text-xs text-stone-500'>Modular dan terukur</p>
                                </div>
                            </div>

                            <div class='mt-3 rounded-2xl border border-stone-200 p-5'>
                                <div class='flex items-center justify-between'>
                                    <p class='text-sm font-extrabold text-stone-900'>Alur sistem</p>
                                    <span class='text-xs font-bold text-forest-600'>Tahap fondasi</span>
                                </div>
                                <div class='mt-5 grid grid-cols-4 gap-2'>
                                    @foreach (['Identitas', 'Lomba', 'Nilai', 'Hasil'] as $index => $step)
                                        <div>
                                            <div class='h-1.5 rounded-full {{ $index === 0 ? 'bg-forest-500' : 'bg-stone-200' }}'></div>
                                            <p class='mt-2 text-[10px] font-bold {{ $index === 0 ? 'text-forest-700' : 'text-stone-400' }}'>{{ $step }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class='bg-white py-20 lg:py-24'>
                <div class='mx-auto max-w-7xl px-5 sm:px-8'>
                    <div class='max-w-2xl'>
                        <p class='text-xs font-extrabold uppercase tracking-[0.2em] text-forest-600'>Satu sistem, peran yang jelas</p>
                        <h2 class='mt-4 text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl'>Dibangun untuk seluruh meja kerja gantangan.</h2>
                        <p class='mt-4 text-base leading-7 text-stone-500'>Setiap pengguna memperoleh akses yang relevan tanpa mencampur tanggung jawab.</p>
                    </div>

                    <div class='mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4'>
                        @foreach ([
                            ['Owner', 'Arahkan pengelolaan sistem dan kontrol akses utama.'],
                            ['Admin', 'Siapkan operasional lomba dan data penyelenggaraan.'],
                            ['Juri', 'Fokus pada penugasan dan alur penilaian yang aman.'],
                            ['Peserta', 'Kelola identitas dan keikutsertaan dari satu akun.'],
                        ] as $index => [$role, $copy])
                            <article class='rounded-2xl border border-stone-200 p-6 transition duration-200 hover:-translate-y-1 hover:border-forest-200 hover:shadow-soft'>
                                <span class='grid size-10 place-items-center rounded-xl {{ $index === 0 ? 'bg-gold-100 text-gold-600' : 'bg-forest-50 text-forest-700' }} text-sm font-extrabold'>0{{ $index + 1 }}</span>
                                <h3 class='mt-5 text-lg font-extrabold text-stone-900'>{{ $role }}</h3>
                                <p class='mt-2 text-sm leading-6 text-stone-500'>{{ $copy }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class='py-20 lg:py-24'>
                <div class='mx-auto max-w-7xl px-5 sm:px-8'>
                    <div class='overflow-hidden rounded-3xl bg-forest-900 px-6 py-10 text-white shadow-soft sm:px-10 lg:flex lg:items-center lg:justify-between lg:px-14 lg:py-14'>
                        <div class='max-w-2xl'>
                            <p class='text-xs font-extrabold uppercase tracking-[0.2em] text-gold-300'>Mulai dari fondasi yang benar</p>
                            <h2 class='mt-4 text-3xl font-extrabold tracking-tight'>Siapkan akun peserta Anda sekarang.</h2>
                            <p class='mt-4 text-sm leading-7 text-forest-100'>Identitas dan akses yang aman menjadi langkah pertama sebelum alur event dan penilaian diaktifkan.</p>
                        </div>
                        <div class='mt-8 flex shrink-0 gap-3 lg:mt-0'>
                            @auth
                                <x-ui.link-button route='dashboard' variant='secondary'>Buka dashboard</x-ui.link-button>
                            @else
                                <x-ui.link-button route='register' variant='secondary'>Daftar peserta</x-ui.link-button>
                            @endauth
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class='border-t border-stone-200 bg-white'>
            <div class='mx-auto flex max-w-7xl flex-col gap-4 px-5 py-8 sm:flex-row sm:items-center sm:justify-between sm:px-8'>
                <x-application-logo />
                <p class='text-xs font-medium text-stone-400'>© {{ now()->year }} GantangScore. Sistem Penilaian Gantangan.</p>
            </div>
        </footer>
    </body>
</html>
