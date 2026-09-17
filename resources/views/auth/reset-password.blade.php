<x-layouts.guest
    title='Kata sandi baru'
    eyebrow='Pemulihan akun'
    heading='Buat kata sandi baru'
    description='Gunakan kata sandi yang kuat dan berbeda dari akun lain.'
>
    <x-ui.status />

    <form method='POST' action='{{ route('password.store') }}' class='mt-6 space-y-5'>
        @csrf
        <input type='hidden' name='token' value='{{ $request->route('token') }}'>

        <x-form.field
            name='email'
            label='Alamat email'
            type='email'
            :value='$request->email'
            autocomplete='email'
            required
        />

        <x-form.field
            name='password'
            label='Kata sandi baru'
            type='password'
            autocomplete='new-password'
            placeholder='Minimal 8 karakter'
            required
        />

        <x-form.field
            name='password_confirmation'
            label='Ulangi kata sandi baru'
            type='password'
            autocomplete='new-password'
            placeholder='Ketik ulang'
            required
        />

        <x-ui.button class='w-full'>Simpan kata sandi baru</x-ui.button>
    </form>
</x-layouts.guest>
