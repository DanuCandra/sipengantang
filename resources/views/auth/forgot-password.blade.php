<x-layouts.guest
    title='Lupa kata sandi'
    eyebrow='Pemulihan akun'
    heading='Atur ulang kata sandi'
    description='Masukkan email akun. Kami akan mengirimkan tautan pemulihan jika email terdaftar.'
>
    <x-ui.status />

    <form method='POST' action='{{ route('password.email') }}' class='mt-6 space-y-5'>
        @csrf

        <x-form.field
            name='email'
            label='Alamat email'
            type='email'
            autocomplete='email'
            placeholder='nama@contoh.id'
            required
            autofocus
        />

        <x-ui.button class='w-full'>Kirim tautan pemulihan</x-ui.button>
    </form>

    <a href='{{ route('login') }}' class='mt-7 flex items-center justify-center gap-2 text-sm font-bold text-forest-700 hover:text-forest-900'>
        <span aria-hidden='true'>←</span>
        Kembali ke halaman masuk
    </a>
</x-layouts.guest>
