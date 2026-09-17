@if (session('status'))
    <div role='status' class='rounded-xl border border-forest-200 bg-forest-50 px-4 py-3 text-sm font-semibold text-forest-800'>
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div role='alert' class='rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700'>
        Mohon periksa kembali data yang ditandai.
    </div>
@endif
