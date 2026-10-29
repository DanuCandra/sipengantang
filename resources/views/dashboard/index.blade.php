<x-layouts.dashboard :area='$area' :user-name='$userName'>
    <div class='mx-auto max-w-7xl'>
        <div class='flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between'>
            <div>
                <div class='inline-flex items-center gap-2 rounded-full border border-forest-200 bg-forest-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.16em] text-forest-700'>
                    <span class='size-2 rounded-full bg-forest-500'></span>
                    Area {{ $area }} aktif
                </div>
                <h1 class='mt-4 text-3xl font-extrabold tracking-tight text-stone-900'>{{ $headline }}</h1>
                <p class='mt-2 max-w-2xl text-sm leading-6 text-stone-500'>{{ $description }}</p>
            </div>

            <div class='w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 shadow-sm sm:w-auto sm:min-w-44'>
                <p class='text-xs font-bold uppercase tracking-wider text-stone-600'>Status akses</p>
                <p class='mt-1 flex items-center gap-2 text-sm font-extrabold text-forest-800'>
                    <span class='size-2 rounded-full bg-forest-500'></span>
                    {{ $userStatus }}
                </p>
            </div>
        </div>

        <section class='mt-7 grid gap-5 lg:grid-cols-[minmax(0,1.45fr)_minmax(18rem,0.55fr)]'>
            <article class='rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6'>
                <div class='flex items-start justify-between gap-4 border-b border-stone-100 pb-5'>
                    <div>
                        <p class='text-xs font-extrabold uppercase tracking-[0.18em] text-forest-600'>Operasional lomba</p>
                        <h2 class='mt-2 text-xl font-extrabold tracking-tight text-stone-900'>{{ $area === 'Owner' ? 'Kelola event dan tim Anda' : 'Siapkan lomba hingga hasil resmi' }}</h2>
                    </div>
                </div>

                @php
                    $dashboardItems = $area === 'Owner' ? [
                        ['Manajemen event', 'Buat, edit, publikasikan, dan pantau seluruh event milik Anda.'],
                        ['Penugasan admin', 'Hubungkan akun Admin secara spesifik ke setiap event.'],
                        ['Kontrol akses', 'Admin hanya dapat membuka event yang benar-benar ditugaskan.'],
                        ['Riwayat aktivitas', 'Perubahan penting tersimpan sebagai audit operasional.'],
                    ] : ($area === 'Admin' ? [
                        ['Event ditugaskan', 'Kelola informasi event sesuai penugasan dari Owner.'],
                        ['Kelas kompetisi', 'Atur jenis burung, biaya, kuota, dan jadwal kelas.'],
                        ['Peserta dan pembayaran', 'Tinjau pendaftaran, verifikasi bukti transfer, dan pantau peserta.'],
                        ['Penjurian dan hasil', 'Pantau progres Juri, selesaikan draw, finalisasi hasil, dan terbitkan piagam pemenang.'],
                    ] : [
                        ['Autentikasi', 'Login, logout, registrasi peserta, dan reset kata sandi.'],
                        ['Kontrol akses', 'Status akun, multi-role, middleware, dan gate dashboard.'],
                        ['Struktur aplikasi', 'Route terpisah per audiens dan komponen Blade reusable.'],
                        ['Kualitas', 'MySQL test database, factory, seeder, dan automated tests.'],
                    ]);
                @endphp

                <div class='mt-2 divide-y divide-stone-100'>
                    @foreach ($dashboardItems as [$title, $copy])
                        <div class='flex gap-3 py-4'>
                            <span class='mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-forest-50 text-forest-700'>
                                <svg aria-hidden='true' class='size-3.5' viewBox='0 0 20 20' fill='none'>
                                    <path d='m4 10 3.5 3.5L16 5' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
                                </svg>
                            </span>
                            <div>
                                <h3 class='text-sm font-extrabold text-stone-900'>{{ $title }}</h3>
                                <p class='mt-1 text-sm leading-6 text-stone-500'>{{ $copy }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <aside class='rounded-2xl bg-forest-900 p-5 text-white shadow-soft sm:p-6'>
                <p class='text-xs font-extrabold uppercase tracking-[0.18em] text-gold-300'>Profil akses</p>
                <p class='mt-3 truncate text-lg font-extrabold'>{{ $userName }}</p>
                <p class='mt-1 text-sm text-forest-200'>Area utama: {{ $area }}</p>

                <div class='mt-6 border-t border-white/10 pt-5'>
                    <p class='text-xs font-bold uppercase tracking-[0.15em] text-forest-200'>Peran terhubung</p>
                    <div class='mt-3 flex flex-wrap gap-2'>
                        @foreach ($roles as $role)
                            <span class='rounded-full bg-white px-3 py-1.5 text-xs font-extrabold text-forest-800'>{{ $role }}</span>
                        @endforeach
                    </div>
                </div>

                <p class='mt-6 border-t border-white/10 pt-5 text-xs leading-5 text-forest-200'>
                    {{ $area === 'Owner' ? 'Buka Event untuk mengelola perlombaan dan menugaskan Admin penanggung jawab.' : 'Buka Event untuk melihat perlombaan yang ditugaskan kepada Anda.' }}
                </p>
            </aside>
        </section>

        <div class='mt-5 flex gap-3 rounded-2xl border border-gold-200 bg-gold-50 p-4 text-sm text-stone-700'>
            <svg aria-hidden='true' class='mt-0.5 size-5 shrink-0 text-gold-600' viewBox='0 0 24 24' fill='none'>
                <path d='M12 8v4m0 4h.01M10.3 4.6 3.5 17a2 2 0 0 0 1.8 3h13.4a2 2 0 0 0 1.8-3L13.7 4.6a2 2 0 0 0-3.4 0Z' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
            </svg>
            <p class='leading-6'><a href="{{ route($area === 'Owner' ? 'owner.events.index' : 'admin.events.index') }}" class="inline-flex min-h-11 items-center font-bold text-forest-800 underline underline-offset-4">Buka daftar event →</a></p>
        </div>
    </div>
</x-layouts.dashboard>
