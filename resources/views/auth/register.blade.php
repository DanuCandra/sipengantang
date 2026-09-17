<x-layouts.guest
    title='Daftar peserta'
    eyebrow='Pendaftaran peserta'
    heading='Buat akun peserta'
    description='Isi identitas dasar Anda. Profil lomba dapat dilengkapi setelah masuk.'
>
    <x-ui.status />

    <form method='POST' action='{{ route('register') }}' class='mt-6 space-y-5'>
        @csrf

        <x-form.field
            name='name'
            label='Nama lengkap'
            autocomplete='name'
            placeholder='Nama sesuai identitas'
            required
            autofocus
        />

        <x-form.field
            name='email'
            label='Alamat email'
            type='email'
            autocomplete='email'
            placeholder='nama@contoh.id'
            required
        />

        <x-form.field
            name='phone'
            label='Nomor telepon'
            type='tel'
            autocomplete='tel'
            placeholder='08xxxxxxxxxx'
        />

        <div class='grid gap-5 sm:grid-cols-2'>
            <x-form.field
                name='password'
                label='Kata sandi'
                type='password'
                autocomplete='new-password'
                placeholder='Minimal 8 karakter'
                required
            />

            <x-form.field
                name='password_confirmation'
                label='Ulangi kata sandi'
                type='password'
                autocomplete='new-password'
                placeholder='Ketik ulang'
                required
            />
        </div>

        <p class='text-xs leading-5 text-stone-500'>
            Dengan mendaftar, Anda membuat akun dengan peran Peserta. Peran operasional dikelola terpisah oleh penyelenggara.
        </p>

        <x-ui.button class='w-full'>Buat akun peserta</x-ui.button>
    </form>

    <p class='mt-7 text-center text-sm text-stone-500'>
        Sudah memiliki akun?
        <a href='{{ route('login') }}' class='font-bold text-forest-700 hover:text-forest-900'>Masuk sekarang</a>
    </p>
</x-layouts.guest>
