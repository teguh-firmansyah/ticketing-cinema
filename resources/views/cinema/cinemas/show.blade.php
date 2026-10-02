<x-cinema-layout>
    <x-slot name="title">{{ $cinema->name }} — {{ setting('app_name') }} Cinema</x-slot>

    {{-- Cinema Hero --}}
    <section class="relative bg-gray-950 pt-8 pb-0 overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96
            bg-blue-600/5 rounded-full blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.015]"
                style="background-image:
                linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px);
                background-size: 60px 60px;">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-gray-600 mb-6">
                <a href="{{ route('cinema.index') }}" class="hover:text-gray-400 transition">Home</a>
                <i class="ti ti-chevron-right text-gray-700 text-xs"></i>
                <a href="{{ route('cinema.cinemas') }}" class="hover:text-gray-400 transition">Bioskop</a>
                <i class="ti ti-chevron-right text-gray-700 text-xs"></i>
                <span class="text-gray-500">{{ $cinema->name }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row items-start gap-8 pb-8">

                {{-- Logo --}}
                <div
                    class="w-20 h-20 bg-white/5 border border-white/10 rounded-3xl
                flex items-center justify-center flex-shrink-0">
                    @if ($cinema->logo)
                        <img src="{{ Storage::url($cinema->logo) }}" class="w-12 h-12 object-contain"
                            alt="{{ $cinema->name }}">
                    @else
                        <i class="ti ti-building text-gray-500 text-4xl"></i>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <h1 class="text-3xl lg:text-4xl font-black text-white mb-2">
                        {{ $cinema->name }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-3 mb-4 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <i class="ti ti-map-pin text-blue-400 text-base"></i>
                            {{ $cinema->address }}
                        </span>
                        @if ($cinema->phone)
                            <span class="text-gray-700">·</span>
                            <a href="tel:{{ $cinema->phone }}"
                                class="flex items-center gap-1.5 hover:text-white transition">
                                <i class="ti ti-phone text-gray-600 text-base"></i>
                                {{ $cinema->phone }}
                            </a>
                        @endif
                    </div>

                    {{-- Studio type badges --}}
                    <div class="flex flex-wrap gap-2 mb-5">
                        @foreach ($cinema->studios->pluck('type')->unique() as $sType)
                            <span
                                class="text-xs font-bold px-3 py-1.5 rounded-full border
                        {{ $sType === 'imax' ? 'bg-blue-900/30 border-blue-500/30 text-blue-400' : '' }}
                        {{ $sType === '4dx' ? 'bg-purple-900/30 border-purple-500/30 text-purple-400' : '' }}
                        {{ $sType === '3d' ? 'bg-cyan-900/30 border-cyan-500/30 text-cyan-400' : '' }}
                        {{ $sType === 'vip' ? 'bg-amber-900/30 border-amber-500/30 text-amber-400' : '' }}
                        {{ $sType === 'premiere' ? 'bg-rose-900/30 border-rose-500/30 text-rose-400' : '' }}
                        {{ $sType === 'regular' ? 'bg-gray-800 border-white/10 text-gray-500' : '' }}">
                                {{ strtoupper($sType) }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap gap-3">
                        @if ($cinema->maps_url)
                            <a href="{{ $cinema->maps_url }}" target="_blank"
                                class="inline-flex items-center gap-2 h-10 px-4 bg-blue-600/20
                            border border-blue-500/30 text-blue-400 text-sm font-medium
                            rounded-xl hover:bg-blue-600/30 transition-all duration-200">
                                <i class="ti ti-map text-base"></i>
                                Lihat di Peta
                            </a>
                        @endif
                        @if ($cinema->phone)
                            <a href="tel:{{ $cinema->phone }}"
                                class="inline-flex items-center gap-2 h-10 px-4 bg-white/5
                            border border-white/10 text-gray-400 text-sm font-medium
                            rounded-xl hover:bg-white/10 hover:text-white
                            transition-all duration-200">
                                <i class="ti ti-phone text-base"></i>
                                Hubungi
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Quick stats --}}
                <div class="grid grid-cols-3 gap-3 flex-shrink-0 lg:w-56">
                    @foreach ([['icon' => 'ti-door', 'value' => $cinema->studios->count(), 'label' => 'Studio'], ['icon' => 'ti-armchair', 'value' => $cinema->studios->sum('total_seats'), 'label' => 'Total Kursi'], ['icon' => 'ti-movie', 'value' => $movies->count(), 'label' => 'Film Tayang']] as $stat)
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-3 text-center">
                            <i class="ti {{ $stat['icon'] }} text-blue-400 text-xl block mb-1"></i>
                            <p class="text-lg font-black text-white">{{ $stat['value'] }}</p>
                            <p class="text-[10px] text-gray-600">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>

            </div>

        </div>
    </section>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left: Showtimes --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Date picker --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h2 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                        <i class="ti ti-calendar text-blue-400"></i>
                        Pilih Tanggal
                    </h2>

                    @if ($availableDates->isEmpty())
                        <div class="text-center py-6">
                            <i class="ti ti-calendar-off text-3xl text-gray-700 block mb-2"></i>
                            <p class="text-sm text-gray-500">
                                Belum ada jadwal tersedia.
                            </p>
                        </div>
                    @else
                        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                            @foreach ($availableDates as $dateItem)
                                <a href="{{ request()->fullUrlWithQuery(['date' => $dateItem['date']]) }}"
                                    class="flex-shrink-0 flex flex-col items-center gap-0.5
                            px-4 py-3 rounded-2xl border transition-all duration-200
                            min-w-[64px]
                            {{ $selectedDate === $dateItem['date']
                                ? 'bg-blue-600 border-blue-600 shadow-lg shadow-blue-600/20'
                                : 'bg-white/5 border-white/5 hover:border-white/20 hover:bg-white/10' }}">
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-wider
                            {{ $selectedDate === $dateItem['date'] ? 'text-blue-200' : 'text-gray-500' }}">
                                        {{ $dateItem['label'] }}
                                    </span>
                                    <span class="text-xl font-black text-white">
                                        {{ $dateItem['day'] }}
                                    </span>
                                    <span
                                        class="text-[10px] font-medium
                            {{ $selectedDate === $dateItem['date'] ? 'text-blue-200' : 'text-gray-600' }}">
                                        {{ $dateItem['month'] }}
                                    </span>
                                    @if ($dateItem['is_today'])
                                        <span
                                            class="text-[9px] font-bold
                            {{ $selectedDate === $dateItem['date'] ? 'text-blue-200' : 'text-blue-400' }}">
                                            Hari Ini
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Showtimes per movie --}}
                @if ($showtimes->isEmpty())
                    <div class="bg-gray-900 border border-white/5 rounded-2xl p-10 text-center">
                        <i class="ti ti-calendar-off text-5xl text-gray-700 block mb-3"></i>
                        <p class="text-base font-semibold text-gray-400 mb-1">
                            Tidak ada jadwal tayang
                        </p>
                        <p class="text-sm text-gray-600">
                            Coba pilih tanggal lain
                        </p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($showtimes as $movieId => $movieShowtimes)
                            @php $movie = $movieShowtimes->first()->movie; @endphp

                            <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">

                                {{-- Movie header --}}
                                <div class="flex items-start gap-4 p-5 border-b border-white/5">
                                    <a href="{{ route('cinema.movies.show', $movie->slug) }}"
                                        class="w-14 h-20 bg-gray-800 rounded-xl overflow-hidden
                                flex-shrink-0 border border-white/5 hover:border-white/10
                                transition-colors duration-200">
                                        <img src="{{ $movie->poster_url }}"
                                            class="w-full h-full object-cover hover:scale-105
                                    transition-transform duration-300"
                                            alt="{{ $movie->title }}" loading="lazy">
                                    </a>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('cinema.movies.show', $movie->slug) }}" class="group">
                                            <h3
                                                class="text-sm font-bold text-white
                                    group-hover:text-blue-400 transition-colors
                                    duration-200 mb-1">
                                                {{ $movie->title }}
                                            </h3>
                                        </a>
                                        <div
                                            class="flex flex-wrap items-center gap-2 mb-2
                                text-xs text-gray-500">
                                            @if ($movie->vote_average)
                                                <span class="flex items-center gap-1 text-amber-400">
                                                    <i class="ti ti-star-filled text-xs"></i>
                                                    {{ $movie->vote_average }}
                                                </span>
                                                <span class="text-gray-700">·</span>
                                            @endif
                                            <span>{{ $movie->duration_formatted }}</span>
                                            <span class="text-gray-700">·</span>
                                            <span>{{ $movie->age_rating }}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach (array_slice($movie->genres ?? [], 0, 3) as $genre)
                                                <span
                                                    class="text-[10px] bg-white/5 border border-white/5
                                    text-gray-600 px-2 py-0.5 rounded-lg">
                                                    {{ $genre }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Showtimes per studio --}}
                                <div class="p-5">
                                    @php
                                        $byStudio = $movieShowtimes->groupBy('studio_id');
                                    @endphp

                                    @foreach ($byStudio as $studioId => $studioShowtimes)
                                        @php $studio = $studioShowtimes->first()->studio; @endphp

                                        <div class="mb-4 last:mb-0">
                                            {{-- Studio badge --}}
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="text-xs font-bold text-white">
                                                    {{ $studio->name }}
                                                </span>
                                                <span
                                                    class="text-[10px] font-bold px-2 py-0.5
                                    rounded-full border
                                    {{ $studio->type === 'imax' ? 'bg-blue-900/30 border-blue-500/30 text-blue-400' : '' }}
                                    {{ $studio->type === '4dx' ? 'bg-purple-900/30 border-purple-500/30 text-purple-400' : '' }}
                                    {{ $studio->type === '3d' ? 'bg-cyan-900/30 border-cyan-500/30 text-cyan-400' : '' }}
                                    {{ $studio->type === 'vip' ? 'bg-amber-900/30 border-amber-500/30 text-amber-400' : '' }}
                                    {{ $studio->type === 'premiere' ? 'bg-rose-900/30 border-rose-500/30 text-rose-400' : '' }}
                                    {{ $studio->type === 'regular' ? 'bg-gray-800 border-white/10 text-gray-500' : '' }}">
                                                    {{ $studio->type_name }}
                                                </span>
                                            </div>

                                            {{-- Time slots --}}
                                            @php
                                                $byFormat = $studioShowtimes->groupBy(
                                                    fn($s) => $s->format . '-' . $s->language,
                                                );
                                            @endphp

                                            @foreach ($byFormat as $formatLang => $fShowtimes)
                                                @php $first = $fShowtimes->first(); @endphp

                                                <div class="mb-3 last:mb-0">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span
                                                            class="text-[10px] font-bold bg-white/5
                                        border border-white/10 text-gray-500
                                        px-2 py-0.5 rounded-lg">
                                                            {{ $first->format_label }}
                                                        </span>
                                                        <span class="text-[10px] text-gray-700">
                                                            {{ $first->language_label }}
                                                        </span>
                                                        <span class="text-[10px] text-gray-700 ml-auto">
                                                            Mulai
                                                            <span class="text-gray-500 font-semibold">
                                                                Rp
                                                                {{ number_format($first->price_regular, 0, ',', '.') }}
                                                            </span>
                                                        </span>
                                                    </div>

                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach ($fShowtimes as $showtime)
                                                            @php
                                                                $isPast = $showtime->start_time->lt(now());
                                                                $isAlmostFull =
                                                                    $showtime->available_seats <= 20 &&
                                                                    $showtime->available_seats > 0;
                                                                $isFull = $showtime->available_seats === 0;
                                                            @endphp

                                                            @if ($isPast || $isFull)
                                                                <div
                                                                    class="flex flex-col items-center px-3.5 py-2.5
                                        bg-white/3 border rounded-xl min-w-[70px]
                                        {{ $isFull ? 'border-red-900/20' : 'border-white/5 opacity-40' }}
                                        cursor-not-allowed">
                                                                    <span
                                                                        class="text-sm font-black
                                            {{ $isFull ? 'text-gray-600 line-through' : 'text-gray-600' }}">
                                                                        {{ $showtime->start_time->format('H:i') }}
                                                                    </span>
                                                                    <span
                                                                        class="text-[9px] font-medium mt-0.5
                                            {{ $isFull ? 'text-red-800' : 'text-gray-700' }}">
                                                                        {{ $isFull ? 'Penuh' : 'Selesai' }}
                                                                    </span>
                                                                </div>
                                                            @else
                                                                <a href="{{ route('cinema.seat-map', $showtime->id) }}"
                                                                    class="group flex flex-col items-center px-3.5 py-2.5
                                            border rounded-xl transition-all duration-200
                                            min-w-[70px]
                                            {{ $isAlmostFull
                                                ? 'border-amber-500/30 hover:border-amber-400/50 hover:bg-amber-500/5'
                                                : 'border-white/10 hover:border-blue-500/50 hover:bg-blue-500/5' }}">
                                                                    <span
                                                                        class="text-sm font-black text-white
                                            group-hover:text-blue-400
                                            transition-colors duration-200">
                                                                        {{ $showtime->start_time->format('H:i') }}
                                                                    </span>
                                                                    <span
                                                                        class="text-[9px] mt-0.5
                                            transition-colors duration-200
                                            {{ $isAlmostFull ? 'text-amber-400 font-semibold' : 'text-gray-600 group-hover:text-blue-400' }}">
                                                                        {{ $isAlmostFull ? $showtime->available_seats . ' sisa' : 'Tersedia' }}
                                                                    </span>
                                                                </a>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>

                                        @if (!$loop->last)
                                            <div class="border-t border-white/5 my-4"></div>
                                        @endif
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-5">

                {{-- Info card --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase
                    tracking-wider mb-4">
                        Informasi Bioskop
                    </h3>
                    <div class="space-y-3">
                        @if ($cinema->description)
                            <p class="text-xs text-gray-500 leading-relaxed">
                                {{ $cinema->description }}
                            </p>
                            <div class="border-t border-white/5 pt-3"></div>
                        @endif

                        @foreach ([['icon' => 'ti-map-pin', 'label' => 'Alamat', 'value' => $cinema->address], ['icon' => 'ti-phone', 'label' => 'Telepon', 'value' => $cinema->phone], ['icon' => 'ti-mail', 'label' => 'Email', 'value' => $cinema->email], ['icon' => 'ti-map-pin-filled', 'label' => 'Kota', 'value' => $cinema->city]] as $info)
                            @if ($info['value'])
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-7 h-7 bg-white/5 rounded-lg flex items-center
                            justify-center flex-shrink-0 mt-0.5">
                                        <i class="ti {{ $info['icon'] }} text-gray-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-600 uppercase tracking-wider">
                                            {{ $info['label'] }}
                                        </p>
                                        <p class="text-xs text-gray-300 mt-0.5 leading-snug">
                                            {{ $info['value'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Studios --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase
                    tracking-wider mb-4">
                        Studio Tersedia
                    </h3>
                    <div class="space-y-3">
                        @foreach ($cinema->studios as $studio)
                            <div
                                class="flex items-center gap-3 p-3 bg-white/5
                        rounded-xl border border-white/5">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="text-xs font-bold text-white">
                                            {{ $studio->name }}
                                        </p>
                                        <span
                                            class="text-[9px] font-bold px-1.5 py-0.5
                                    rounded border
                                    {{ $studio->type === 'imax' ? 'bg-blue-900/30 border-blue-500/30 text-blue-400' : '' }}
                                    {{ $studio->type === '4dx' ? 'bg-purple-900/30 border-purple-500/30 text-purple-400' : '' }}
                                    {{ $studio->type === '3d' ? 'bg-cyan-900/30 border-cyan-500/30 text-cyan-400' : '' }}
                                    {{ $studio->type === 'vip' ? 'bg-amber-900/30 border-amber-500/30 text-amber-400' : '' }}
                                    {{ $studio->type === 'premiere' ? 'bg-rose-900/30 border-rose-500/30 text-rose-400' : '' }}
                                    {{ $studio->type === 'regular' ? 'bg-gray-800 border-white/10 text-gray-500' : '' }}">
                                            {{ strtoupper($studio->type) }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-gray-600">
                                        {{ $studio->total_seats }} kursi
                                    </p>
                                </div>
                                @if ($studio->facilities)
                                    <div class="flex items-center gap-1">
                                        @foreach (array_slice($studio->facilities ?? [], 0, 2) as $fac)
                                            <div class="w-5 h-5 bg-white/5 rounded-md flex items-center
                                justify-center"
                                                title="{{ str_replace('_', ' ', $fac) }}">
                                                <i class="ti ti-star text-gray-700 text-[9px]"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Facilities --}}
                @if ($cinema->facilities)
                    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                        <h3
                            class="text-xs font-semibold text-gray-500 uppercase
                    tracking-wider mb-3">
                            Fasilitas
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($cinema->facilities as $fac)
                                @php
                                    $facConfig = [
                                        'parking' => ['icon' => 'ti-car', 'label' => 'Parkir'],
                                        'food_court' => ['icon' => 'ti-tools-kitchen', 'label' => 'Food Court'],
                                        'atm' => ['icon' => 'ti-building-bank', 'label' => 'ATM'],
                                        'wifi' => ['icon' => 'ti-wifi', 'label' => 'WiFi'],
                                        'prayer_room' => ['icon' => 'ti-building-mosque', 'label' => 'Musholla'],
                                        'nursing_room' => ['icon' => 'ti-heart', 'label' => 'Ruang Ibu'],
                                        'handicap_access' => ['icon' => 'ti-accessible', 'label' => 'Akses Difabel'],
                                        'vip_lounge' => ['icon' => 'ti-armchair', 'label' => 'VIP Lounge'],
                                    ];
                                    $fc = $facConfig[$fac] ?? [
                                        'icon' => 'ti-star',
                                        'label' => ucfirst(str_replace('_', ' ', $fac)),
                                    ];
                                @endphp
                                <div class="flex items-center gap-2 bg-white/5 rounded-xl p-2.5">
                                    <div
                                        class="w-7 h-7 bg-blue-600/10 rounded-lg flex items-center
                            justify-center flex-shrink-0">
                                        <i class="ti {{ $fc['icon'] }} text-blue-400 text-sm"></i>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $fc['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Maps --}}
                @if ($cinema->maps_url)
                    <a href="{{ $cinema->maps_url }}" target="_blank"
                        class="flex items-center gap-3 bg-gray-900 border border-white/5
                    hover:border-blue-500/30 rounded-2xl p-4 transition-all duration-200
                    group">
                        <div
                            class="w-10 h-10 bg-blue-600/10 border border-blue-500/20
                    rounded-xl flex items-center justify-center flex-shrink-0
                    group-hover:bg-blue-600/20 transition-colors duration-200">
                            <i class="ti ti-map-2 text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <p
                                class="text-sm font-semibold text-white
                        group-hover:text-blue-400 transition-colors duration-200">
                                Buka di Google Maps
                            </p>
                            <p class="text-xs text-gray-600">Lihat lokasi bioskop</p>
                        </div>
                        <i
                            class="ti ti-external-link text-gray-700 text-base ml-auto
                    group-hover:text-blue-400 transition-colors duration-200"></i>
                    </a>
                @endif

                {{-- Now showing movies --}}
                @if ($movies->count() > 0)
                    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                        <h3
                            class="text-xs font-semibold text-gray-500 uppercase
                    tracking-wider mb-4">
                            Sedang Tayang di Sini
                        </h3>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($movies->take(6) as $movie)
                                <a href="{{ route('cinema.movies.show', $movie->slug) }}" class="group">
                                    <div
                                        class="aspect-[2/3] bg-gray-800 rounded-xl overflow-hidden
                            border border-white/5 group-hover:border-blue-500/30
                            transition-colors duration-200">
                                        <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}"
                                            loading="lazy"
                                            class="w-full h-full object-cover group-hover:scale-105
                                    transition-transform duration-300">
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .scrollbar-hide::-webkit-scrollbar {
                display: none;
            }

            .scrollbar-hide {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
    @endpush

</x-cinema-layout>
