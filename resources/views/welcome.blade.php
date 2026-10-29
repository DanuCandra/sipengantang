<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='description' content='GantangScore menyelenggarakan kompetisi burung berkicau yang profesional, tertib, dan berkelas.'>
        <title>{{ config('app.name', 'GantangScore') }} — Event Kompetisi Burung Berkicau</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class='overflow-x-hidden bg-canvas antialiased'>
        <header class='sticky top-0 z-30 border-b border-stone-200/70 bg-canvas/95 backdrop-blur-xl'>
            <nav aria-label='Navigasi utama' class='mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-5 sm:px-8'>
                <a href='{{ route('home') }}' aria-label='Beranda GantangScore' class='shrink-0 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4'>
                    <x-application-logo class='[&>span:last-child]:hidden sm:[&>span:last-child]:block' />
                </a>

                <div class='hidden items-center gap-7 lg:flex'>
                    <a href='{{ route('events.index') }}' class='text-sm font-bold text-stone-600 transition hover:text-forest-700'>Agenda</a>
                    <a href='#tentang' class='text-sm font-bold text-stone-600 transition hover:text-forest-700'>Tentang kami</a>
                    <a href='#cara-ikut' class='text-sm font-bold text-stone-600 transition hover:text-forest-700'>Cara ikut</a>
                    <a href='#hasil' class='text-sm font-bold text-stone-600 transition hover:text-forest-700'>Hasil event</a>
                </div>

                <div class='hidden shrink-0 items-center gap-2 lg:flex'>
                    @auth
                        <x-ui.link-button route='dashboard'>Dashboard</x-ui.link-button>
                    @else
                        <x-ui.link-button route='login' variant='ghost'>Masuk</x-ui.link-button>
                        <x-ui.link-button route='register'>Portal peserta</x-ui.link-button>
                    @endauth
                </div>

                <button data-mobile-nav-toggle type='button' aria-expanded='false' aria-controls='mobile-navigation' aria-label='Buka menu' class='grid size-11 place-items-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm lg:hidden'>
                    <svg aria-hidden='true' class='size-5' viewBox='0 0 24 24' fill='none'>
                        <path d='M4 7h16M4 12h16M4 17h16' stroke='currentColor' stroke-width='2' stroke-linecap='round'/>
                    </svg>
                </button>
            </nav>
        </header>

        <div data-mobile-nav-backdrop class='fixed inset-0 z-40 hidden bg-stone-950/40 backdrop-blur-sm lg:hidden'></div>
        <aside id='mobile-navigation' data-mobile-nav role='dialog' aria-modal='true' aria-label='Menu navigasi mobile' class='fixed inset-y-0 right-0 z-50 hidden w-[min(21rem,88vw)] flex-col bg-white p-6 shadow-2xl lg:hidden'>
                <div class='flex items-center justify-between gap-4 border-b border-stone-100 pb-5'>
                    <x-application-logo compact />
                    <button data-mobile-nav-close type='button' aria-label='Tutup menu' class='grid size-11 place-items-center rounded-xl border border-stone-200 text-stone-600'>
                        <svg aria-hidden='true' class='size-5' viewBox='0 0 24 24' fill='none'>
                            <path d='m6 6 12 12M18 6 6 18' stroke='currentColor' stroke-width='2' stroke-linecap='round'/>
                        </svg>
                    </button>
                </div>

                <nav class='mt-6 flex flex-col gap-2' aria-label='Navigasi mobile'>
                    @foreach ([['Agenda', route('events.index')], ['Tentang kami', '#tentang'], ['Cara ikut', '#cara-ikut'], ['Hasil event', '#hasil']] as [$label, $href])
                        <a data-mobile-nav-link href='{{ $href }}' class='rounded-xl px-4 py-3 text-sm font-bold text-stone-700 transition hover:bg-forest-50 hover:text-forest-700'>{{ $label }}</a>
                    @endforeach
                </nav>

                <div class='mt-auto grid gap-3 border-t border-stone-100 pt-6'>
                    @auth
                        <x-ui.link-button route='dashboard'>Buka dashboard</x-ui.link-button>
                    @else
                        <x-ui.link-button route='register'>Portal peserta</x-ui.link-button>
                        <x-ui.link-button route='login' variant='secondary'>Masuk</x-ui.link-button>
                    @endauth
                </div>
        </aside>

        <main>
            <section class='relative overflow-hidden pb-16 pt-10 sm:pb-20 sm:pt-14 lg:pb-24 lg:pt-16'>
                <div class='absolute -left-40 top-12 size-[32rem] rounded-full bg-forest-200/35 blur-3xl'></div>
                <div class='absolute -right-40 top-0 size-[32rem] rounded-full bg-gold-100/70 blur-3xl'></div>

                <div class='relative mx-auto grid max-w-7xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(30rem,1.1fr)] lg:gap-16'>
                    <div>
                        <div class='inline-flex items-center gap-2 rounded-full border border-forest-200 bg-white/85 px-4 py-2 text-[11px] font-extrabold uppercase tracking-[0.15em] text-forest-700 shadow-sm sm:text-xs'>
                            <span class='size-2 rounded-full bg-gold-400'></span>
                            Event organizer burung berkicau
                        </div>

                        <h1 class='mt-6 max-w-2xl text-4xl font-extrabold leading-[1.08] tracking-tight text-stone-900 sm:text-5xl lg:text-6xl'>
                            Panggung berkelas untuk
                            <span class='text-forest-600'>suara terbaik.</span>
                        </h1>
                        <p class='mt-6 max-w-xl text-base leading-8 text-stone-600 sm:text-lg'>
                            GantangScore menghadirkan kompetisi burung berkicau yang tertib, kompetitif, dan layak dikenang—untuk peserta, komunitas, dan para juara.
                        </p>

                        <div class='mt-8 flex flex-col gap-3 sm:flex-row'>
                            <a href='#agenda' class='inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-forest-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-forest-900/10 transition hover:bg-forest-700 focus-visible:outline-2 focus-visible:outline-offset-2'>
                                Lihat agenda event
                                <svg aria-hidden='true' class='size-4' viewBox='0 0 24 24' fill='none'>
                                    <path d='m9 18 6-6-6-6' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
                                </svg>
                            </a>
                            <a href='#tentang' class='inline-flex min-h-12 items-center justify-center rounded-xl border border-stone-200 bg-white px-6 py-3 text-sm font-bold text-stone-700 transition hover:border-forest-300 hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2'>Kenal lebih dekat</a>
                        </div>

                        <div class='mt-8 grid gap-3 text-sm font-semibold text-stone-600 sm:grid-cols-3'>
                            @foreach (['Penjurian tertib', 'Kelas kompetitif', 'Informasi transparan'] as $item)
                                <span class='inline-flex items-center gap-2'>
                                    <svg aria-hidden='true' class='size-4 shrink-0 text-forest-600' viewBox='0 0 20 20' fill='none'>
                                        <path d='m4 10 3.5 3.5L16 5' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
                                    </svg>
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class='relative mx-auto w-full max-w-2xl'>
                        <div class='absolute -inset-5 rounded-[2rem] bg-forest-200/35 blur-2xl'></div>
                        <figure class='relative overflow-hidden rounded-3xl border-4 border-white bg-stone-200 shadow-soft'>
                            <img
                                src='{{ asset('images/gantangscore-hero.webp') }}'
                                alt='Burung Murai Batu dalam suasana kompetisi gantangan yang tertib'
                                class='aspect-[4/3] h-full w-full object-cover object-center sm:aspect-[16/10] lg:aspect-[5/4]'
                                width='1672'
                                height='940'
                                fetchpriority='high'
                            >
                            <div class='absolute inset-x-0 bottom-0 hidden bg-gradient-to-t from-forest-950/90 via-forest-950/40 to-transparent px-6 pb-6 pt-16 pr-80 text-white sm:block'>
                                <p class='text-xs font-extrabold uppercase tracking-[0.18em] text-gold-300'>Kompetisi dengan standar</p>
                                <p class='mt-2 text-lg font-extrabold sm:text-xl'>Tertib di arena. Terpercaya di hasil.</p>
                            </div>
                        </figure>

                        <div class='absolute -bottom-5 left-4 right-4 flex items-center gap-3 rounded-2xl border border-white bg-white/95 p-4 shadow-soft backdrop-blur sm:left-auto sm:right-6 sm:w-72'>
                            <span class='grid size-10 shrink-0 place-items-center rounded-xl bg-gold-100 text-gold-600'>
                                <svg aria-hidden='true' class='size-5' viewBox='0 0 24 24' fill='none'>
                                    <path d='M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
                                </svg>
                            </span>
                            <div>
                                <p class='text-[10px] font-extrabold uppercase tracking-[0.16em] text-forest-600'>Event berikutnya</p>
                                <p class='mt-1 text-sm font-extrabold text-stone-900'>Segera diumumkan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id='agenda' class='scroll-mt-24 bg-white py-20 lg:py-24'>
                <div class='mx-auto max-w-7xl px-5 sm:px-8'>
                    <div class='flex flex-col gap-5 md:flex-row md:items-end md:justify-between'>
                        <div class='max-w-2xl'>
                            <p class='text-xs font-extrabold uppercase tracking-[0.2em] text-forest-600'>Agenda GantangScore</p>
                            <h2 class='mt-4 text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl'>Temukan gantangan berikutnya.</h2>
                            <p class='mt-4 text-base leading-7 text-stone-500'>Tanggal, kota, venue, kelas, dan pembukaan pendaftaran akan selalu diumumkan di sini.</p>
                        </div>
                        <span class='inline-flex w-fit items-center gap-2 rounded-full bg-forest-50 px-4 py-2 text-xs font-extrabold text-forest-700'>
                            <span class='size-2 rounded-full bg-gold-400'></span>
                            Agenda resmi
                        </span>
                    </div>

                    <div class='mt-10 grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(18rem,0.5fr)]'>
                        @if ($featuredEvent)
                            <article class='overflow-hidden rounded-3xl border border-stone-200 bg-canvas p-6 shadow-sm sm:p-8'>
                                @if ($featuredEvent['banner_url'])<img src="{{ $featuredEvent['banner_url'] }}" alt="Banner event {{ $featuredEvent['name'] }}" decoding="async" class="mb-6 max-h-96 w-full rounded-2xl bg-stone-100 object-contain">@else<p class="mb-5 text-xs font-semibold text-stone-600">Banner belum tersedia</p>@endif
                                <p class='text-xs font-extrabold uppercase tracking-[0.18em] text-forest-600'>Event terdekat</p>
                                <h3 class='mt-3 text-2xl font-extrabold text-stone-900'>{{ $featuredEvent['name'] }}</h3>
                                <div class='mt-6 grid gap-4 sm:grid-cols-2'>
                                    <p class='text-sm font-bold text-stone-700'>{{ $featuredEvent['date'] }}</p>
                                    <p class='text-sm font-bold text-stone-700'>{{ $featuredEvent['location'] }}</p>
                                </div>
                                <div class='mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-forest-100 pt-5'>
                                    <span class='text-sm font-bold text-stone-500'>{{ $featuredEvent['classes'] }} kelas kompetisi</span>
                                    <a href='{{ $featuredEvent['url'] }}' class='inline-flex min-h-11 items-center rounded-xl bg-forest-600 px-5 text-sm font-bold text-white hover:bg-forest-700'>Lihat detail event</a>
                                </div>
                            </article>
                        @else
                            <article class='rounded-3xl border border-forest-200 bg-gradient-to-br from-forest-50 to-white p-6 shadow-sm sm:p-8'>
                                <div class='flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between'>
                                    <div class='max-w-xl'>
                                        <span class='inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-extrabold text-forest-700 shadow-sm'>
                                            <span class='size-2 rounded-full bg-gold-400'></span>
                                            Dalam persiapan
                                        </span>
                                        <h3 class='mt-5 text-2xl font-extrabold tracking-tight text-stone-900'>Seri event berikutnya sedang kami siapkan.</h3>
                                        <p class='mt-3 text-sm leading-7 text-stone-600'>Belum ada jadwal yang dipublikasikan. Informasi kota, venue, kelas lomba, dan pendaftaran akan tampil di halaman ini setelah diumumkan resmi.</p>
                                    </div>
                                    <span class='grid size-14 shrink-0 place-items-center rounded-2xl bg-forest-600 text-white shadow-lg shadow-forest-900/10'>
                                        <svg aria-hidden='true' class='size-7' viewBox='0 0 24 24' fill='none'>
                                            <path d='M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
                                        </svg>
                                    </span>
                                </div>

                                <div class='mt-7 grid gap-3 border-t border-forest-100 pt-6 sm:grid-cols-3'>
                                    @foreach ([['Tanggal', 'Segera diumumkan'], ['Lokasi', 'Segera diumumkan'], ['Pendaftaran', 'Belum dibuka']] as [$label, $value])
                                        <div class='rounded-2xl bg-white p-4'>
                                            <p class='text-[10px] font-extrabold uppercase tracking-[0.15em] text-stone-400'>{{ $label }}</p>
                                            <p class='mt-2 text-sm font-extrabold text-stone-800'>{{ $value }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endif

                        <aside class='rounded-3xl bg-forest-900 p-6 text-white sm:p-8'>
                            <p class='text-xs font-extrabold uppercase tracking-[0.18em] text-gold-300'>Info lengkap event</p>
                            <h3 class='mt-3 text-xl font-extrabold'>Semua yang peserta butuhkan, dalam satu pengumuman.</h3>
                            <ul class='mt-6 space-y-4 text-sm text-forest-100'>
                                @foreach (['Jadwal dan lokasi venue', 'Daftar kelas dan kuota', 'Biaya serta periode pendaftaran', 'Hasil resmi setelah lomba'] as $item)
                                    <li class='flex gap-3'>
                                        <svg aria-hidden='true' class='mt-0.5 size-4 shrink-0 text-gold-300' viewBox='0 0 20 20' fill='none'>
                                            <path d='m4 10 3.5 3.5L16 5' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
                                        </svg>
                                        {{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        </aside>
                    </div>
                </div>
            </section>

            <section id='tentang' class='scroll-mt-24 py-20 lg:py-24'>
                <div class='mx-auto max-w-7xl px-5 sm:px-8'>
                    <div class='grid gap-10 lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)] lg:gap-16'>
                        <div>
                            <p class='text-xs font-extrabold uppercase tracking-[0.2em] text-forest-600'>Tentang GantangScore</p>
                            <h2 class='mt-4 text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl'>Bukan sekadar lomba. Sebuah panggung yang dijaga kualitasnya.</h2>
                            <p class='mt-5 text-base leading-8 text-stone-600'>Kami membangun pengalaman kompetisi yang profesional dari pengumuman event, proses pendaftaran, penjurian, hingga hasil akhir.</p>
                        </div>

                        <div class='grid gap-4 sm:grid-cols-3'>
                            @foreach ([
                                ['01', 'Profesional', 'Alur event yang tertib dan informasi yang mudah dipahami.'],
                                ['02', 'Kompetitif', 'Arena yang menghargai performa, disiplin, dan sportivitas.'],
                                ['03', 'Terpercaya', 'Penilaian dan hasil disajikan dengan status yang jelas.'],
                            ] as [$number, $title, $copy])
                                <article class='rounded-2xl border border-stone-200 bg-white p-6 shadow-sm'>
                                    <span class='grid size-10 place-items-center rounded-xl bg-forest-50 text-xs font-extrabold text-forest-700'>{{ $number }}</span>
                                    <h3 class='mt-6 text-lg font-extrabold text-stone-900'>{{ $title }}</h3>
                                    <p class='mt-3 text-sm leading-6 text-stone-500'>{{ $copy }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id='cara-ikut' class='scroll-mt-24 bg-forest-900 py-20 text-white lg:py-24'>
                <div class='mx-auto max-w-7xl px-5 sm:px-8'>
                    <div class='max-w-2xl'>
                        <p class='text-xs font-extrabold uppercase tracking-[0.2em] text-gold-300'>Cara mengikuti event</p>
                        <h2 class='mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl'>Dari pengumuman hingga naik gantangan.</h2>
                        <p class='mt-4 text-base leading-7 text-forest-100'>Ikuti setiap langkah melalui informasi resmi GantangScore.</p>
                    </div>

                    <ol class='mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4'>
                        @foreach ([
                            ['Pantau agenda', 'Lihat tanggal, kota, venue, dan kelas yang tersedia.'],
                            ['Siapkan akun', 'Lengkapi identitas peserta dan data burung.'],
                            ['Daftar kelas', 'Pilih kelas dan ikuti proses konfirmasi pendaftaran.'],
                            ['Datang & berlomba', 'Bawa bukti pendaftaran dan ikuti arahan panitia.'],
                        ] as $index => [$title, $copy])
                            <li class='rounded-2xl border border-white/10 bg-white/[0.07] p-6'>
                                <span class='text-sm font-extrabold text-gold-300'>0{{ $index + 1 }}</span>
                                <h3 class='mt-5 text-lg font-extrabold'>{{ $title }}</h3>
                                <p class='mt-3 text-sm leading-6 text-forest-100'>{{ $copy }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            <section id='hasil' class='scroll-mt-24 bg-white py-20 lg:py-24'>
                <div class='mx-auto max-w-7xl px-5 sm:px-8'>
                    <div class='grid items-center gap-8 rounded-3xl border border-stone-200 bg-canvas p-6 sm:p-10 lg:grid-cols-[minmax(0,1fr)_auto] lg:p-12'>
                        <div class='max-w-2xl'>
                            <p class='text-xs font-extrabold uppercase tracking-[0.2em] text-forest-600'>Hasil & dokumentasi</p>
                            <h2 class='mt-4 text-3xl font-extrabold tracking-tight text-stone-900'>Setiap juara punya jejak yang layak dilihat.</h2>
                            <p class='mt-4 text-base leading-7 text-stone-500'>Hasil resmi, peringkat, dan dokumentasi event akan tersedia setelah kompetisi GantangScore selesai.</p>
                        </div>
                        <div class='grid size-20 place-items-center rounded-3xl bg-gold-100 text-gold-600'>
                            <svg aria-hidden='true' class='size-9' viewBox='0 0 24 24' fill='none'>
                                <path d='M8 4h8v5a4 4 0 0 1-8 0V4Zm4 9v5m-4 2h8M8 6H4v2a4 4 0 0 0 4 4m8-6h4v2a4 4 0 0 1-4 4' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            <section class='pb-20 lg:pb-24'>
                <div class='mx-auto max-w-7xl px-5 sm:px-8'>
                    <div class='overflow-hidden rounded-3xl bg-forest-900 px-6 py-10 text-white shadow-soft sm:px-10 lg:flex lg:items-center lg:justify-between lg:px-14 lg:py-14'>
                        <div class='max-w-2xl'>
                            <p class='text-xs font-extrabold uppercase tracking-[0.2em] text-gold-300'>Bersiap untuk event berikutnya</p>
                            <h2 class='mt-4 text-3xl font-extrabold tracking-tight'>Jadilah bagian dari panggung GantangScore.</h2>
                            <p class='mt-4 text-sm leading-7 text-forest-100'>Buat akun peserta sekarang agar proses pendaftaran lebih siap saat agenda baru diumumkan.</p>
                        </div>
                        <div class='mt-8 flex flex-col gap-3 sm:flex-row lg:mt-0'>
                            @auth
                                <x-ui.link-button route='dashboard' variant='secondary'>Buka dashboard</x-ui.link-button>
                            @else
                                <x-ui.link-button route='register' variant='secondary'>Buat akun peserta</x-ui.link-button>
                                <x-ui.link-button route='login' class='border border-white/20'>Masuk</x-ui.link-button>
                            @endauth
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class='border-t border-stone-200 bg-white'>
            <div class='mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:px-8 md:grid-cols-[minmax(0,1fr)_auto] md:items-end'>
                <div>
                    <x-application-logo />
                    <p class='mt-4 max-w-md text-sm leading-6 text-stone-500'>Penyelenggara kompetisi burung berkicau yang profesional, kompetitif, dan terpercaya.</p>
                </div>
                <div class='md:text-right'>
                    <p class='text-xs font-bold uppercase tracking-[0.16em] text-stone-400'>Informasi resmi event</p>
                    <p class='mt-2 text-sm font-semibold text-stone-600'>Dipublikasikan melalui website GantangScore.</p>
                    <p class='mt-4 text-xs text-stone-400'>© {{ now()->year }} GantangScore. Seluruh hak dilindungi.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
