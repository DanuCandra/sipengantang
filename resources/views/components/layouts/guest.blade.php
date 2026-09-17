@props([
    'title',
    'eyebrow' => 'Sistem penilaian gantangan',
    'heading',
    'description',
])

<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='description' content='GantangScore membantu penyelenggara, juri, dan peserta menjalankan penilaian lomba secara tertib.'>
        <title>{{ $title }} — {{ config('app.name', 'GantangScore') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class='min-h-screen antialiased'>
        <main class='grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(30rem,0.78fr)]'>
            <section class='relative hidden overflow-hidden bg-forest-900 px-10 py-12 text-white lg:flex lg:flex-col xl:px-16'>
                <div class='absolute -left-20 top-1/3 size-72 rounded-full bg-forest-500/20 blur-3xl'></div>
                <div class='absolute -right-20 -top-16 size-80 rounded-full bg-gold-400/15 blur-3xl'></div>

                <a href='{{ route('home') }}' class='relative z-10 w-fit rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white'>
                    <x-application-logo class='[&>span:last-child>span]:text-white [&>span:last-child>span:last-child]:text-forest-200' />
                </a>

                <div class='relative z-10 my-auto max-w-xl py-14'>
                    <div class='mb-8 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-forest-100'>
                        <span class='size-2 rounded-full bg-gold-400'></span>
                        Fondasi operasional lomba
                    </div>
                    <h1 class='text-4xl font-extrabold leading-tight tracking-tight xl:text-5xl'>
                        Penilaian yang lebih tenang, dari meja panitia hingga peserta.
                    </h1>
                    <p class='mt-6 max-w-lg text-base leading-8 text-forest-100'>
                        Satu ruang kerja untuk identitas pengguna, peran, dan akses yang aman—siap dikembangkan ke alur lomba pada tahap berikutnya.
                    </p>

                    <div class='mt-10 grid grid-cols-3 gap-3'>
                        @foreach (['Akses berbasis peran', 'Data peserta rapi', 'Siap untuk lomba'] as $item)
                            <div class='rounded-2xl border border-white/10 bg-white/[0.07] p-4'>
                                <svg aria-hidden='true' class='mb-4 size-5 text-gold-300' viewBox='0 0 24 24' fill='none'>
                                    <path d='m5 12 4 4L19 6' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
                                </svg>
                                <p class='text-sm font-semibold leading-5 text-white'>{{ $item }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <p class='relative z-10 text-xs font-medium text-forest-300'>
                    GantangScore · Sistem Penilaian Gantangan
                </p>
            </section>

            <section class='flex min-h-screen flex-col bg-white px-5 py-6 sm:px-10 lg:px-14 xl:px-20'>
                <div class='flex items-center justify-between lg:justify-end'>
                    <a href='{{ route('home') }}' class='lg:hidden'>
                        <x-application-logo />
                    </a>
                    <a href='{{ route('home') }}' class='text-sm font-bold text-stone-500 transition hover:text-forest-700'>
                        Kembali ke beranda
                    </a>
                </div>

                <div class='mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-12'>
                    <p class='text-xs font-extrabold uppercase tracking-[0.2em] text-forest-600'>{{ $eyebrow }}</p>
                    <h2 class='mt-3 text-3xl font-extrabold tracking-tight text-stone-900'>{{ $heading }}</h2>
                    <p class='mt-3 text-sm leading-6 text-stone-500'>{{ $description }}</p>

                    <div class='mt-8'>
                        {{ $slot }}
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
