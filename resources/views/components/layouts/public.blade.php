@props(['title'])
<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
<head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'><meta name='description' content='Agenda resmi kompetisi burung berkicau GantangScore.'><title>{{ $title }} — {{ config('app.name', 'GantangScore') }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class='min-h-screen bg-canvas antialiased'>
    <header class='sticky top-0 z-30 border-b border-stone-200/70 bg-white/95 backdrop-blur-xl'><nav class='mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-5 sm:px-8' aria-label='Navigasi utama'><a href='{{ route("home") }}' class='rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4'><x-application-logo /></a><div class='flex items-center gap-2'><a href='{{ route("events.index") }}' class='hidden rounded-xl px-4 py-2 text-sm font-bold text-stone-600 hover:bg-forest-50 hover:text-forest-700 sm:inline-flex'>Agenda</a>@auth<x-ui.link-button route='dashboard'>Dashboard</x-ui.link-button>@else<x-ui.link-button route='login' variant='ghost'>Masuk</x-ui.link-button><x-ui.link-button route='register' class='hidden sm:inline-flex'>Daftar</x-ui.link-button>@endauth</div></nav></header>
    <main>{{ $slot }}</main>
    <footer class='mt-16 border-t border-stone-200 bg-white'><div class='mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-stone-500 sm:flex-row sm:items-center sm:justify-between sm:px-8'><x-application-logo compact /><p>Kompetisi tertib, hasil terpercaya.</p></div></footer>
</body>
</html>
