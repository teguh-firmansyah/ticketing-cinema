<x-cinema-layout>
    <x-slot name="title">{{ $movie->title }} — {{ config('app.name') }} Cinema</x-slot>
    <x-slot name="description">{{ Str::limit($movie->synopsis, 160) }}</x-slot>
    <x-slot name="ogImage">{{ $movie->backdrop_url }}</x-slot>

    {{-- Hero Backdrop --}}
    <section class="relative overflow-hidden">

        {{-- Backdrop --}}
        <div class="absolute inset-0 h-[500px]">
            <img src="{{ $movie->backdrop_url }}" class="w-full h-full object-cover object-center"
                alt="{{ $movie->title }}">
            <div class="absolute inset-0 bg-gradient-to-r
            from-gray-950 via-gray-950/80 to-gray-950/40">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t
            from-gray-950 via-gray-950/20 to-transparent">
            </div>
        </div>

        {{-- Content --}}
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-0">
            <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">

                {{-- Poster --}}
                <div class="flex-shrink-0 mx-auto lg:mx-0">
                    <div
                        class="w-48 lg:w-56 rounded-2xl overflow-hidden shadow-2xl
                    shadow-black/60 border border-white/10">
                        <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}"
                            class="w-full aspect-[2/3] object-cover">
                    </div>
                </div>

                {{-- Info --}}
                <div class="flex-1 pt-0 lg:pt-8 text-center lg:text-left">

                    {{-- Status badge --}}
                    <div class="flex items-center gap-2 justify-center lg:justify-start mb-4">
                        @if ($movie->status === 'now_showing')
                            <span
                                class="inline-flex items-center gap-1.5 bg-red-600 text-white
                        text-xs font-bold px-3 py-1.5 rounded-full">
                                <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                                Sedang Tayang
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 bg-amber-500/20
                        text-amber-400 border border-amber-500/30 text-xs font-bold
                        px-3 py-1.5 rounded-full">
                                <i class="ti ti-clock text-xs"></i>
                                Segera Hadir —
                                {{ $movie->release_date?->translatedFormat('d M Y') }}
                            </span>
                        @endif

                        {{-- Age rating --}}
                        <span
                            class="text-xs font-black px-3 py-1.5 rounded-full border
                        {{ $movie->age_rating === 'SU' ? 'border-green-500/50 text-green-400 bg-green-900/20' : '' }}
                        {{ $movie->age_rating === '13+' ? 'border-blue-500/50 text-blue-400 bg-blue-900/20' : '' }}
                        {{ $movie->age_rating === '17+' ? 'border-amber-500/50 text-amber-400 bg-amber-900/20' : '' }}
                        {{ $movie->age_rating === '21+' ? 'border-red-500/50 text-red-400 bg-red-900/20' : '' }}">
                            {{ $movie->age_rating }}
                        </span>
                    </div>

                    {{-- Title --}}
                    <h1
                        class="text-3xl lg:text-5xl font-black text-white leading-tight
                    tracking-tight mb-2">
                        {{ $movie->title }}
                    </h1>

                    @if ($movie->original_title && $movie->original_title !== $movie->title)
                        <p class="text-gray-500 text-sm italic mb-4">
                            {{ $movie->original_title }}
                        </p>
                    @endif

                    {{-- Rating & Meta --}}
                    <div
                        class="flex flex-wrap items-center gap-3 justify-center
                    lg:justify-start mb-5 text-sm">
                        @if ($movie->vote_average)
                            <div class="flex items-center gap-1.5">
                                @for ($s = 1; $s <= 5; $s++)
                                    <i
                                        class="ti text-sm
                            {{ $s <= round($movie->vote_average / 2) ? 'ti-star-filled text-amber-400' : 'ti-star text-gray-700' }}"></i>
                                @endfor
                                <span class="text-amber-400 font-bold ml-1">
                                    {{ $movie->vote_average }}
                                </span>
                                @if ($movie->vote_count)
                                    <span class="text-gray-600 text-xs">
                                        ({{ number_format($movie->vote_count) }} ulasan)
                                    </span>
                                @endif
                            </div>
                            <span class="text-gray-700">·</span>
                        @endif

                        @if ($movie->duration)
                            <span class="flex items-center gap-1.5 text-gray-400">
                                <i class="ti ti-clock text-gray-600"></i>
                                {{ $movie->duration_formatted }}
                            </span>
                            <span class="text-gray-700">·</span>
                        @endif

                        @if ($movie->language)
                            <span class="flex items-center gap-1.5 text-gray-400">
                                <i class="ti ti-language text-gray-600"></i>
                                {{ strtoupper($movie->language) }}
                            </span>
                        @endif
                    </div>

                    {{-- Genres --}}
                    <div class="flex flex-wrap gap-2 justify-center lg:justify-start mb-5">
                        @foreach ($movie->genres ?? [] as $genre)
                            <a href="{{ route('cinema.movies', ['genre' => $genre]) }}"
                                class="text-xs font-medium bg-white/5 hover:bg-white/10
                            border border-white/10 hover:border-white/20 text-gray-400
                            hover:text-white px-3 py-1.5 rounded-xl
                            transition-all duration-200">
                                {{ $genre }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Synopsis --}}
                    <p
                        class="text-gray-400 text-sm leading-relaxed max-w-2xl
                    mx-auto lg:mx-0 mb-6 line-clamp-4 lg:line-clamp-none">
                        {{ $movie->synopsis }}
                    </p>

                    {{-- Details grid --}}
                    <div
                        class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-7
                    text-left max-w-lg mx-auto lg:mx-0">
                        @if ($movie->director)
                            <div class="bg-white/5 rounded-xl p-3">
                                <p class="text-[10px] text-gray-600 uppercase tracking-wider mb-1">
                                    Sutradara
                                </p>
                                <p class="text-xs font-semibold text-white">
                                    {{ $movie->director }}
                                </p>
                            </div>
                        @endif

                        @if ($movie->release_date)
                            <div class="bg-white/5 rounded-xl p-3">
                                <p class="text-[10px] text-gray-600 uppercase tracking-wider mb-1">
                                    Rilis
                                </p>
                                <p class="text-xs font-semibold text-white">
                                    {{ $movie->release_date->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if ($movie->duration)
                            <div class="bg-white/5 rounded-xl p-3">
                                <p class="text-[10px] text-gray-600 uppercase tracking-wider mb-1">
                                    Durasi
                                </p>
                                <p class="text-xs font-semibold text-white">
                                    {{ $movie->duration_formatted }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- CTAs --}}
                    <div class="flex flex-wrap items-center gap-3
                    justify-center lg:justify-start">
                        @if ($movie->status === 'now_showing')
                            <a href="#showtimes"
                                class="inline-flex items-center gap-2 h-12 px-7 bg-red-600
                            hover:bg-red-500 text-white font-bold text-sm rounded-xl
                            transition-all duration-200 shadow-lg shadow-red-600/20">
                                <i class="ti ti-ticket text-base"></i>
                                Pesan Tiket
                            </a>
                        @endif

                        @if ($movie->hasTrailer())
                            <button onclick="document.getElementById('trailer-modal').classList.remove('hidden')"
                                class="inline-flex items-center gap-2 h-12 px-5 bg-white/10
                            hover:bg-white/20 border border-white/10 text-white font-medium
                            text-sm rounded-xl transition-all duration-200">
                                <div
                                    class="w-6 h-6 bg-white rounded-full flex items-center
                            justify-center flex-shrink-0">
                                    <i class="ti ti-player-play text-gray-900 text-xs ml-0.5"></i>
                                </div>
                                Tonton Trailer
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- Spacer --}}
        <div class="h-48 lg:h-32"></div>

    </section>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left: Showtimes --}}
            <div class="lg:col-span-2 space-y-6" id="showtimes">

                @if ($movie->status === 'now_showing')

                    {{-- Date Picker --}}
                    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                        <h2 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                            <i class="ti ti-calendar text-red-500"></i>
                            Pilih Tanggal Tayang
                        </h2>

                        @if ($availableDates->isEmpty())
                            <div class="text-center py-8">
                                <i class="ti ti-calendar-off text-3xl text-gray-700 block mb-2"></i>
                                <p class="text-sm text-gray-500">
                                    Belum ada jadwal tersedia untuk film ini.
                                </p>
                            </div>
                        @else
                            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                                @foreach ($availableDates as $dateItem)
                                    <a href="{{ request()->fullUrlWithQuery(['date' => $dateItem['date']]) }}"
                                        class="flex-shrink-0 flex flex-col items-center gap-0.5 px-4 py-3
                            rounded-2xl border transition-all duration-200 min-w-[64px]
                            {{ $selectedDate === $dateItem['date']
                                ? 'bg-red-600 border-red-600 shadow-lg shadow-red-600/20'
                                : 'bg-white/5 border-white/5 hover:border-white/20
                                                               hover:bg-white/10' }}">
                                        <span
                                            class="text-[10px] font-semibold uppercase tracking-wider
                            {{ $selectedDate === $dateItem['date'] ? 'text-red-200' : 'text-gray-500' }}">
                                            {{ $dateItem['label'] }}
                                        </span>
                                        <span
                                            class="text-xl font-black
                            {{ $selectedDate === $dateItem['date'] ? 'text-white' : 'text-white' }}">
                                            {{ $dateItem['day'] }}
                                        </span>
                                        <span
                                            class="text-[10px] font-medium
                            {{ $selectedDate === $dateItem['date'] ? 'text-red-200' : 'text-gray-600' }}">
                                            {{ $dateItem['month'] }}
                                        </span>
                                        @if ($dateItem['is_today'])
                                            <span
                                                class="text-[9px] font-bold
                            {{ $selectedDate === $dateItem['date'] ? 'text-red-200' : 'text-red-500' }}">
                                                Hari Ini
                                            </span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Showtime list per cinema --}}
                    @if ($showtimes->isEmpty())
                        <div class="bg-gray-900 border border-white/5 rounded-2xl p-8 text-center">
                            <i class="ti ti-clock-off text-4xl text-gray-700 block mb-3"></i>
                            <p class="text-sm font-semibold text-gray-400 mb-1">
                                Tidak ada jadwal pada tanggal ini
                            </p>
                            <p class="text-xs text-gray-600">
                                Coba pilih tanggal lain di atas
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($cinemas as $cinemaId => $cinema)
                                @php $cinemaShowtimes = $showtimes->get($cinemaId, collect()); @endphp

                                <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden">

                                    {{-- Cinema header --}}
                                    <div class="flex items-start gap-4 p-5 border-b border-white/5">
                                        <div
                                            class="w-10 h-10 bg-white/5 rounded-xl flex items-center
                            justify-center flex-shrink-0">
                                            @if ($cinema->logo)
                                                <img src="{{ Storage::url($cinema->logo) }}"
                                                    class="w-6 h-6 object-contain" alt="">
                                            @else
                                                <i class="ti ti-building text-gray-500 text-lg"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-bold text-white">
                                                {{ $cinema->name }}
                                            </h3>
                                            <p class="text-xs text-gray-500 truncate mt-0.5">
                                                <i class="ti ti-map-pin text-xs mr-1"></i>
                                                {{ $cinema->address }}
                                            </p>
                                            @if ($cinema->facilities)
                                                <div class="flex flex-wrap gap-1.5 mt-2">
                                                    @foreach (array_slice($cinema->facilities, 0, 4) as $facility)
                                                        <span
                                                            class="text-[10px] bg-white/5 text-gray-600
                                    px-2 py-0.5 rounded-full border border-white/5">
                                                            {{ str_replace('_', ' ', $facility) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @if ($cinema->maps_url)
                                            <a href="{{ $cinema->maps_url }}" target="_blank"
                                                class="flex-shrink-0 flex items-center gap-1.5 text-xs
                                text-blue-400 hover:text-blue-300 transition-colors">
                                                <i class="ti ti-map text-sm"></i>
                                                <span class="hidden sm:block">Peta</span>
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Showtimes --}}
                                    <div class="p-5">
                                        @php
                                            $groupedByStudio = $cinemaShowtimes->groupBy(fn($s) => $s->studio_id);
                                        @endphp

                                        @foreach ($groupedByStudio as $studioId => $studioShowtimes)
                                            @php $studio = $studioShowtimes->first()->studio; @endphp

                                            <div class="mb-5 last:mb-0">
                                                {{-- Studio label --}}
                                                <div class="flex items-center gap-2 mb-3">
                                                    <span class="text-xs font-bold text-white">
                                                        {{ $studio->name }}
                                                    </span>
                                                    <span
                                                        class="text-[10px] font-semibold px-2 py-0.5
                                    rounded-full border
                                    {{ $studio->type === 'imax' ? 'bg-blue-900/30 border-blue-500/30 text-blue-400' : '' }}
                                    {{ $studio->type === '4dx' ? 'bg-purple-900/30 border-purple-500/30 text-purple-400' : '' }}
                                    {{ $studio->type === '3d' ? 'bg-cyan-900/30 border-cyan-500/30 text-cyan-400' : '' }}
                                    {{ $studio->type === 'vip' ? 'bg-amber-900/30 border-amber-500/30 text-amber-400' : '' }}
                                    {{ $studio->type === 'premiere' ? 'bg-rose-900/30 border-rose-500/30 text-rose-400' : '' }}
                                    {{ $studio->type === 'regular' ? 'bg-gray-800 border-white/10 text-gray-400' : '' }}">
                                                        {{ $studio->type_name }}
                                                    </span>
                                                </div>

                                                {{-- Time slots per format + language --}}
                                                @php
                                                    $byFormat = $studioShowtimes->groupBy(
                                                        fn($s) => $s->format . '-' . $s->language,
                                                    );
                                                @endphp

                                                @foreach ($byFormat as $formatLang => $formatShowtimes)
                                                    @php $first = $formatShowtimes->first(); @endphp

                                                    <div class="mb-3 last:mb-0">
                                                        {{-- Format & Language badge --}}
                                                        <div class="flex items-center gap-2 mb-2.5">
                                                            <span
                                                                class="text-[10px] font-bold bg-white/5
                                        border border-white/10 text-gray-500
                                        px-2 py-0.5 rounded-lg">
                                                                {{ $first->format_label }}
                                                            </span>
                                                            <span class="text-[10px] text-gray-600">
                                                                {{ $first->language_label }}
                                                            </span>
                                                            {{-- Price info --}}
                                                            <span class="text-[10px] text-gray-700 ml-auto">
                                                                Mulai
                                                                <span class="text-gray-500 font-semibold">
                                                                    Rp
                                                                    {{ number_format($first->price_regular, 0, ',', '.') }}
                                                                </span>
                                                            </span>
                                                        </div>

                                                        {{-- Time buttons --}}
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach ($formatShowtimes as $showtime)
                                                                @php
                                                                    $isPast = $showtime->start_time->lt(now());
                                                                    $isAlmostFull =
                                                                        $showtime->available_seats <= 20 &&
                                                                        $showtime->available_seats > 0;
                                                                    $isFull = $showtime->available_seats === 0;
                                                                @endphp

                                                                @if ($isPast)
                                                                    <div
                                                                        class="flex flex-col items-center px-3.5 py-2.5
                                        bg-white/3 border border-white/5 rounded-xl
                                        opacity-40 cursor-not-allowed min-w-[70px]">
                                                                        <span class="text-sm font-black text-gray-600">
                                                                            {{ $showtime->start_time->format('H:i') }}
                                                                        </span>
                                                                        <span class="text-[9px] text-gray-700 mt-0.5">
                                                                            Selesai
                                                                        </span>
                                                                    </div>
                                                                @elseif($isFull)
                                                                    <div
                                                                        class="flex flex-col items-center px-3.5 py-2.5
                                        bg-white/3 border border-red-900/30 rounded-xl
                                        cursor-not-allowed min-w-[70px]">
                                                                        <span
                                                                            class="text-sm font-black text-gray-600 line-through">
                                                                            {{ $showtime->start_time->format('H:i') }}
                                                                        </span>
                                                                        <span
                                                                            class="text-[9px] text-red-800 mt-0.5 font-medium">
                                                                            Penuh
                                                                        </span>
                                                                    </div>
                                                                @else
                                                                    <a href="{{ route('cinema.seat-map', $showtime->id) }}"
                                                                        class="group flex flex-col items-center px-3.5 py-2.5
                                            border rounded-xl transition-all duration-200
                                            min-w-[70px] relative overflow-hidden
                                            {{ $isAlmostFull
                                                ? 'border-amber-500/40 hover:border-amber-500/60 hover:bg-amber-500/5'
                                                : 'border-white/10 hover:border-red-500/50 hover:bg-red-500/5' }}">
                                                                        <span
                                                                            class="text-sm font-black text-white
                                            group-hover:text-red-400 transition-colors duration-200">
                                                                            {{ $showtime->start_time->format('H:i') }}
                                                                        </span>
                                                                        <span
                                                                            class="text-[9px] mt-0.5 transition-colors duration-200
                                            {{ $isAlmostFull ? 'text-amber-400 font-semibold' : 'text-gray-600 group-hover:text-red-500' }}">
                                                                            {{ $isAlmostFull ? $showtime->available_seats . ' tersisa' : 'Tersedia' }}
                                                                        </span>

                                                                        {{-- Subtle hover bg --}}
                                                                        <div
                                                                            class="absolute inset-0 bg-red-600/0
                                            group-hover:bg-red-600/5
                                            transition-colors duration-200">
                                                                        </div>
                                                                    </a>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- Divider between studios --}}
                                            @if (!$loop->last)
                                                <div class="border-t border-white/5 my-4"></div>
                                            @endif
                                        @endforeach
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Cast section --}}
                    @if ($movie->cast && count($movie->cast) > 0)
                        <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                            <h2 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                                <i class="ti ti-users text-red-500"></i>
                                Pemeran
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($movie->cast as $actor)
                                    <div
                                        class="flex items-center gap-2 bg-white/5 border border-white/5
                        rounded-xl px-3 py-2 hover:border-white/10 transition-colors">
                                        <div
                                            class="w-7 h-7 bg-gradient-to-br from-gray-700 to-gray-800
                            rounded-full flex items-center justify-center text-[10px]
                            font-bold text-white flex-shrink-0 border border-white/10">
                                            {{ strtoupper(substr($actor, 0, 1)) }}
                                        </div>
                                        <span class="text-xs text-gray-300 font-medium">{{ $actor }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Coming soon state --}}
                    <div class="bg-gray-900 border border-amber-500/20 rounded-2xl p-8 text-center">
                        <div
                            class="w-16 h-16 bg-amber-500/10 rounded-2xl flex items-center
                    justify-center mx-auto mb-4">
                            <i class="ti ti-clock text-amber-400 text-3xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Film Segera Hadir</h3>
                        @if ($movie->release_date)
                            <p class="text-amber-400 text-sm font-semibold mb-2">
                                {{ $movie->release_date->translatedFormat('d F Y') }}
                            </p>
                        @endif
                        <p class="text-gray-500 text-sm max-w-sm mx-auto">
                            Jadwal tayang akan tersedia mendekati tanggal rilis.
                            Pantau terus untuk informasi terbaru!
                        </p>
                        @if ($movie->hasTrailer())
                            <button onclick="document.getElementById('trailer-modal').classList.remove('hidden')"
                                class="mt-5 inline-flex items-center gap-2 h-10 px-5 bg-amber-500/10
                        border border-amber-500/30 text-amber-400 text-sm font-medium
                        rounded-xl hover:bg-amber-500/20 transition-all duration-200">
                                <i class="ti ti-player-play text-base"></i>
                                Tonton Trailer
                            </button>
                        @endif
                    </div>
                @endif

            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-5">

                {{-- Info card --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
                        Informasi Film
                    </h3>
                    <div class="space-y-3">
                        @foreach ([['label' => 'Status', 'value' => $movie->status_label, 'icon' => 'ti-info-circle'], ['label' => 'Durasi', 'value' => $movie->duration_formatted, 'icon' => 'ti-clock'], ['label' => 'Bahasa', 'value' => strtoupper($movie->language), 'icon' => 'ti-language'], ['label' => 'Rating Usia', 'value' => $movie->age_rating, 'icon' => 'ti-user-check'], ['label' => 'Genre', 'value' => $movie->genres_string, 'icon' => 'ti-category'], ['label' => 'Sutradara', 'value' => $movie->director ?? '-', 'icon' => 'ti-movie'], ['label' => 'Rilis', 'value' => $movie->release_date?->translatedFormat('d M Y') ?? '-', 'icon' => 'ti-calendar']] as $info)
                            @if ($info['value'])
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-7 h-7 bg-white/5 rounded-lg flex items-center
                            justify-center flex-shrink-0 mt-0.5">
                                        <i class="ti {{ $info['icon'] }} text-gray-600 text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[10px] text-gray-600 uppercase tracking-wider">
                                            {{ $info['label'] }}
                                        </p>
                                        <p class="text-xs text-gray-300 font-medium mt-0.5 leading-snug">
                                            {{ $info['value'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Production companies --}}
                @if ($movie->production_companies && count($movie->production_companies) > 0)
                    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                            Produksi
                        </h3>
                        <div class="space-y-2">
                            @foreach ($movie->production_companies as $company)
                                <div class="flex items-center gap-2 text-xs text-gray-400">
                                    <i class="ti ti-building text-gray-700 text-sm flex-shrink-0"></i>
                                    {{ $company }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Share --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Bagikan Film
                    </h3>
                    <div class="flex gap-2">
                        @foreach ([['href' => 'https://wa.me/?text=' . urlencode($movie->title . ' — ' . request()->url())
                            , 'icon' => 'ti-brand-whatsapp', 'color' => 'hover:bg-emerald-500/20 hover:text-emerald-400
                            hover:border-emerald-500/30'], ['href' =>
                            'https://twitter.com/intent/tweet?text=' . urlencode($movie->title) . '&url=' .
                            urlencode(request()->url()), 'icon' => 'ti-brand-x', 'color' => 'hover:bg-white/10
                            hover:text-white'], ['href' =>
                            'https://www.facebook.com/sharer/sharer.php?u=' . urlencode(request()->url()), 'icon' =>
                            'ti-brand-facebook', 'color' => 'hover:bg-blue-500/20 hover:text-blue-400
                            hover:border-blue-500/30']] as $social)
                            <a href="{{ $social['href'] }}" target="_blank"
                                class="flex-1 h-10 border border-white/10 rounded-xl flex items-center
                            justify-center text-gray-500 text-base
                            {{ $social['color'] }} transition-all duration-200">
                                <i class="ti {{ $social['icon'] }}"></i>
                            </a>
                        @endforeach
                        <button
                            onclick="navigator.clipboard.writeText(window.location.href);
                        this.innerHTML='<i class=\'ti ti-check text-emerald-400\'></i>';
                        setTimeout(() => this.innerHTML='<i class=\'ti ti-copy\'></i>', 2000)"
                            class="flex-1 h-10 border border-white/10 rounded-xl flex items-center
                            justify-center text-gray-500 hover:bg-white/5 hover:text-gray-300
                            transition-all duration-200">
                            <i class="ti ti-copy"></i>
                        </button>
                    </div>
                </div>

                {{-- Related movies --}}
                @if ($related->count() > 0)
                    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
                            Film Terkait
                        </h3>
                        <div class="space-y-3">
                            @foreach ($related as $rel)
                                <a href="{{ route('cinema.movies.show', $rel->slug) }}"
                                    class="flex items-center gap-3 group">
                                    <div
                                        class="w-12 h-16 bg-gray-800 rounded-xl overflow-hidden
                            flex-shrink-0 border border-white/5
                            group-hover:border-white/10 transition-colors duration-200">
                                        <img src="{{ $rel->poster_url }}" alt="{{ $rel->title }}" loading="lazy"
                                            class="w-full h-full object-cover group-hover:scale-110
                                    transition-transform duration-300">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-xs font-bold text-white line-clamp-2 leading-snug
                                group-hover:text-red-400 transition-colors duration-200">
                                            {{ $rel->title }}
                                        </p>
                                        <p class="text-[10px] text-gray-600 mt-0.5 truncate">
                                            {{ $rel->genres_string }}
                                        </p>
                                        @if ($rel->vote_average)
                                            <div class="flex items-center gap-1 mt-1">
                                                <i class="ti ti-star-filled text-amber-400 text-[10px]"></i>
                                                <span class="text-[10px] text-amber-400 font-semibold">
                                                    {{ $rel->vote_average }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <span
                                        class="text-[10px] font-bold px-2 py-0.5 rounded-lg border
                            flex-shrink-0
                            {{ $rel->status === 'now_showing' ? 'bg-red-900/30 border-red-500/30 text-red-400' : 'bg-amber-900/30 border-amber-500/30 text-amber-400' }}">
                                        {{ $rel->status === 'now_showing' ? 'Tayang' : 'Segera' }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Trailer Modal --}}
    @if ($movie->hasTrailer())
        <div id="trailer-modal"
            class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-sm
        flex items-center justify-center p-4"
            onclick="if(event.target === this) closeTrailer()">

            <div class="w-full max-w-4xl">
                {{-- Close --}}
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-white">
                        {{ $movie->title }} — Trailer
                    </p>
                    <button onclick="closeTrailer()"
                        class="w-9 h-9 bg-white/10 hover:bg-white/20 border border-white/10
                    rounded-xl flex items-center justify-center text-white
                    transition-all duration-200">
                        <i class="ti ti-x text-base"></i>
                    </button>
                </div>

                {{-- Video --}}
                <div
                    class="relative aspect-video bg-gray-900 rounded-2xl overflow-hidden
            border border-white/10">
                    <iframe id="trailer-iframe" src=""
                        data-src="{{ $movie->youtube_embed }}?autoplay=1&rel=0" class="w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write;
                    encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            // Trailer modal
            function closeTrailer() {
                const modal = document.getElementById('trailer-modal');
                const iframe = document.getElementById('trailer-iframe');
                if (iframe) {
                    iframe.src = '';
                }
                modal.classList.add('hidden');
            }

            document.getElementById('trailer-modal')?.addEventListener('click', function(e) {
                if (e.target === this) closeTrailer();
            });

            // Open trailer — load src saat dibuka
            document.querySelectorAll('[onclick*="trailer-modal"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const iframe = document.getElementById('trailer-iframe');
                    const dataSrc = iframe?.dataset.src;
                    if (iframe && dataSrc && !iframe.src.includes('youtube')) {
                        iframe.src = dataSrc;
                    }
                });
            });

            // Keyboard escape untuk tutup modal
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeTrailer();
            });
        </script>

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
    @endpush

</x-cinema-layout>
