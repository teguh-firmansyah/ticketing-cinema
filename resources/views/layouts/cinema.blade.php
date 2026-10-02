<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <title>{{ $title ?? setting('app_name', config('app.name')) . ' Cinema' }}</title>
    <meta name="description"
        content="{{ $description ?? setting('seo_description', 'Platform tiket bioskop online terpercaya') }}">

    {{-- OG --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? setting('app_name') . ' Cinema' }}">
    <meta property="og:description" content="{{ $description ?? setting('seo_description') }}">
    @if ($ogImage ?? setting('seo_og_image'))
        <meta property="og:image" content="{{ isset($ogImage) ? $ogImage : Storage::url(setting('seo_og_image')) }}">
    @endif

    {{-- Favicon --}}
    <link rel="icon" type="image/png"
        href="{{ setting('app_favicon') ? Storage::url(setting('app_favicon')) : asset('favicon/Logo.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
    @stack('head')
</head>

<body class="font-sans antialiased bg-gray-950 text-gray-100 min-h-screen flex flex-col">

    {{-- Navbar --}}
    @include('layouts.partials.navbar-bioskop')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5" x-data="{ show: true }" x-show="show" x-transition>
            <div
                class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20
            text-emerald-400 text-sm px-4 py-3 rounded-xl">
                <i class="ti ti-circle-check text-base flex-shrink-0"></i>
                {{ session('success') }}
                <button @click="show = false"
                    class="ml-auto text-emerald-500
                hover:text-emerald-300 transition">
                    <i class="ti ti-x text-sm"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5" x-data="{ show: true }" x-show="show" x-transition>
            <div
                class="flex items-center gap-3 bg-red-500/10 border border-red-500/20
            text-red-400 text-sm px-4 py-3 rounded-xl">
                <i class="ti ti-circle-x text-base flex-shrink-0"></i>
                {{ session('error') }}
                <button @click="show = false"
                    class="ml-auto text-red-400
                hover:text-red-300 transition">
                    <i class="ti ti-x text-sm"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    @include('layouts.partials.footer-bioskop')

    @stack('scripts')
</body>

</html>
