<x-layouts.guest
    title='Masuk'
    heading='Selamat datang kembali'
    description='Masuk untuk melanjutkan ke ruang kerja sesuai peran Anda.'
>
    <x-ui.status />

    <form method='POST' action='{{ route('login') }}' class='mt-6 space-y-5'>
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

        <x-form.field
            name='password'
            label='Kata sandi'
            type='password'
            autocomplete='current-password'
            placeholder='Masukkan kata sandi'
            required
        />

        <div class='flex items-center justify-between gap-4'>
            <label class='inline-flex items-center gap-2.5 text-sm font-medium text-stone-600'>
                <input name='remember' type='checkbox' value='1' class='size-4 rounded border-stone-300 text-forest-600 focus:ring-forest-500'>
                Ingat saya
            </label>

            <a href='{{ route('password.request') }}' class='text-sm font-bold text-forest-700 hover:text-forest-900'>
                Lupa kata sandi?
            </a>
        </div>

        <x-ui.button class='w-full'>Masuk ke dashboard</x-ui.button>
    </form>

    <p class='mt-7 text-center text-sm text-stone-500'>
        Belum punya akun?
        <a href='{{ route('register') }}' class='font-bold text-forest-700 hover:text-forest-900'>Daftar sebagai peserta</a>
    </p>
</x-layouts.guest>
