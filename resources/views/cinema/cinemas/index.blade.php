<x-cinema-layout>
    <x-slot name="title">Bioskop — {{ setting('app_name') }} Cinema</x-slot>

    {{-- Page Header --}}
    <section class="relative bg-gray-950 pt-10 pb-16 overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96
            bg-blue-600/5 rounded-full blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.02]"
                style="background-image:
                linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px);
                background-size: 60px 60px;">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center gap-2 bg-blue-500/10 border
                border-blue-500/20 text-blue-400 text-xs font-semibold px-4 py-2
                rounded-full mb-4">
                    <i class="ti ti-building text-sm"></i>
                    Jaringan Bioskop
                </div>
                <h1 class="text-4xl font-black text-white mb-3">
                    Temukan Bioskop Terdekat
                </h1>
                <p class="text-gray-500 text-base max-w-md mx-auto">
                    {{ $stats['total'] }} bioskop di {{ $stats['cities'] }} kota
                    dengan {{ $stats['studios'] }} studio siap melayani Anda.
                </p>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4 max-w-sm mx-auto mb-8">
                @foreach ([['value' => $stats['total'], 'label' => 'Bioskop', 'icon' => 'ti-building'], ['value' => $stats['cities'], 'label' => 'Kota', 'icon' => 'ti-map-pin'], ['value' => $stats['studios'], 'label' => 'Studio', 'icon' => 'ti-movie']] as $stat)
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-center">
                        <i class="ti {{ $stat['icon'] }} text-blue-400 text-xl block mb-1"></i>
                        <p class="text-xl font-black text-white">{{ $stat['value'] }}</p>
                        <p class="text-xs text-gray-600">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Search --}}
            <form method="GET" class="max-w-2xl mx-auto">
                <div class="flex gap-2">
                    <div
                        class="flex items-center gap-2 bg-white/5 border border-white/10
                    rounded-2xl px-4 h-12 flex-1 focus-within:border-white/30
                    transition-all duration-200">
                        <i class="ti ti-search text-gray-500 text-base flex-shrink-0"></i>
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Cari nama atau alamat bioskop..."
                            class="bg-transparent border-none outline-none text-sm
                            text-white placeholder-gray-500 w-full">
                        @if ($search)
                            <a href="{{ route('cinema.cinemas', array_merge(request()->except('search', 'page'))) }}"
                                class="text-gray-600 hover:text-white transition flex-shrink-0">
                                <i class="ti ti-x text-sm"></i>
                            </a>
                        @endif
                    </div>
                    <button type="submit"
                        class="h-12 px-5 bg-blue-600 hover:bg-blue-500 text-white
                        text-sm font-semibold rounded-2xl transition-all duration-200
                        flex items-center gap-2">
                        <i class="ti ti-search text-base"></i>
                        Cari
                    </button>
                </div>
            </form>

        </div>
    </section>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-4 pb-12">

        {{-- Filter Bar --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4 mb-6">
            <div class="flex flex-wrap gap-3 items-center">

                {{-- City filter --}}
                <div class="flex items-center gap-2 flex-1 flex-wrap">
                    <span class="text-xs text-gray-600 flex-shrink-0">Kota:</span>
                    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                        <a href="{{ route('cinema.cinemas', array_merge(request()->except('city', 'page'))) }}"
                            class="flex-shrink-0 h-8 px-3 text-xs font-medium rounded-xl
                            transition-all duration-200 flex items-center
                            {{ !$city
                                ? 'bg-blue-600 text-white'
                                : 'bg-white/5 border border-white/10 text-gray-400
                                                               hover:text-white hover:border-white/20' }}">
                            Semua Kota
                        </a>
                        @foreach ($cities as $c)
                            <a href="{{ route('cinema.cinemas', array_merge(request()->except('city', 'page'), ['city' => $c])) }}"
                                class="flex-shrink-0 h-8 px-3 text-xs font-medium rounded-xl
                            transition-all duration-200 flex items-center
                            {{ $city === $c
                                ? 'bg-blue-600 text-white'
                                : 'bg-white/5 border border-white/10 text-gray-400
                                                               hover:text-white hover:border-white/20' }}">
                                {{ $c }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Studio type filter --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-xs text-gray-600">Tipe:</span>
                    <select name="type" onchange="window.location.href = updateUrlParam('type', this.value)"
                        class="bg-gray-800 border border-white/10 text-gray-300 text-xs
                        rounded-xl px-3 h-8 outline-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="imax" {{ $type === 'imax' ? 'selected' : '' }}>IMAX</option>
                        <option value="4dx" {{ $type === '4dx' ? 'selected' : '' }}>4DX</option>
                        <option value="3d" {{ $type === '3d' ? 'selected' : '' }}>3D</option>
                        <option value="vip" {{ $type === 'vip' ? 'selected' : '' }}>VIP</option>
                        <option value="premiere" {{ $type === 'premiere' ? 'selected' : '' }}>Premiere</option>
                    </select>
                </div>

            </div>
        </div>

        {{-- Results --}}
        @if ($cinemas->isEmpty())
            <div class="text-center py-20 bg-gray-900/50 border border-white/5 rounded-2xl">
                <i class="ti ti-building-off text-5xl text-gray-700 block mb-4"></i>
                <p class="text-base font-semibold text-gray-400 mb-2">
                    Bioskop tidak ditemukan
                </p>
                <p class="text-sm text-gray-600 mb-5">
                    Coba ubah filter atau cari dengan kata kunci berbeda
                </p>
                <a href="{{ route('cinema.cinemas') }}"
                    class="inline-flex items-center gap-2 h-10 px-5 bg-blue-600
                hover:bg-blue-500 text-white text-sm font-medium rounded-xl
                transition-all duration-200">
                    <i class="ti ti-refresh text-base"></i>
                    Reset Filter
                </a>
            </div>
        @else
            {{-- Group by city --}}
            @php
                $groupedCinemas = $cinemas->groupBy('city');
            @endphp

            @foreach ($groupedCinemas as $cityName => $cityCinemas)
                <div class="mb-10">

                    {{-- City header --}}
                    <div class="flex items-center gap-3 mb-5">
                        <div
                            class="w-9 h-9 bg-blue-600/20 border border-blue-500/30 rounded-xl
                flex items-center justify-center flex-shrink-0">
                            <i class="ti ti-map-pin text-blue-400 text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">{{ $cityName }}</h2>
                            <p class="text-xs text-gray-600">
                                {{ $cityCinemas->count() }} bioskop tersedia
                            </p>
                        </div>
                    </div>

                    {{-- Cinema cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($cityCinemas as $cinema)
                            @php
                                $cinemaShowtimes = $nowShowingPerCinema->get($cinema->id, collect());
                                $studioTypes = $cinema->studios->pluck('type')->unique();
                            @endphp

                            <a href="{{ route('cinema.cinemas.show', $cinema->id) }}"
                                class="group bg-gray-900 border border-white/5 rounded-2xl overflow-hidden
                    hover:border-blue-500/30 hover:shadow-xl hover:shadow-blue-500/5
                    transition-all duration-300 hover:-translate-y-0.5 flex flex-col">

                                {{-- Header --}}
                                <div class="flex items-start gap-4 p-5 border-b border-white/5">

                                    {{-- Logo --}}
                                    <div
                                        class="w-14 h-14 bg-white/5 border border-white/10 rounded-2xl
                        flex items-center justify-center flex-shrink-0
                        group-hover:border-blue-500/30 group-hover:bg-blue-500/5
                        transition-all duration-300">
                                        @if ($cinema->logo)
                                            <img src="{{ Storage::url($cinema->logo) }}" class="w-9 h-9 object-contain"
                                                alt="{{ $cinema->name }}">
                                        @else
                                            <i
                                                class="ti ti-building text-gray-600 text-2xl
                            group-hover:text-blue-400 transition-colors duration-300"></i>
                                        @endif
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <h3
                                            class="text-sm font-bold text-white leading-snug mb-1
                            group-hover:text-blue-400 transition-colors duration-200 truncate">
                                            {{ $cinema->name }}
                                        </h3>
                                        <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                            <i class="ti ti-map-pin text-[10px] mr-1"></i>
                                            {{ $cinema->address }}
                                        </p>
                                    </div>

                                    {{-- Arrow --}}
                                    <i
                                        class="ti ti-arrow-right text-gray-700 text-base flex-shrink-0 mt-1
                        group-hover:text-blue-400 group-hover:translate-x-0.5
                        transition-all duration-200"></i>

                                </div>

                                {{-- Studio types --}}
                                <div class="px-5 py-3 flex flex-wrap gap-1.5">
                                    @foreach ($studioTypes as $sType)
                                        <span
                                            class="text-[10px] font-bold px-2.5 py-1 rounded-full border
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

                                {{-- Now showing today --}}
                                @if ($cinemaShowtimes->count() > 0)
                                    <div class="px-5 pb-4 mt-auto">
                                        <p class="text-[10px] text-gray-600 uppercase tracking-wider mb-2">
                                            Tayang Hari Ini
                                        </p>
                                        <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                                            @foreach ($cinemaShowtimes->take(4) as $st)
                                                <div
                                                    class="flex-shrink-0 w-10 h-14 bg-gray-800 rounded-lg
                            overflow-hidden border border-white/5">
                                                    <img src="{{ $st->movie->poster_url }}"
                                                        class="w-full h-full object-cover"
                                                        alt="{{ $st->movie->title }}" loading="lazy">
                                                </div>
                                            @endforeach
                                            @if ($cinemaShowtimes->count() > 4)
                                                <div
                                                    class="flex-shrink-0 w-10 h-14 bg-gray-800 rounded-lg
                            border border-white/10 flex items-center justify-center">
                                                    <span class="text-[10px] text-gray-600 font-bold">
                                                        +{{ $cinemaShowtimes->count() - 4 }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="px-5 pb-4 mt-auto">
                                        <p class="text-[10px] text-gray-700 italic">
                                            Tidak ada jadwal hari ini
                                        </p>
                                    </div>
                                @endif

                                {{-- Footer --}}
                                <div
                                    class="px-5 py-3 border-t border-white/5 bg-white/[0.02] mt-auto
                    flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-xs text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <i class="ti ti-door text-gray-700 text-xs"></i>
                                            {{ $cinema->studios->count() }} studio
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="ti ti-armchair text-gray-700 text-xs"></i>
                                            {{ $cinema->studios->sum('total_seats') }} kursi
                                        </span>
                                    </div>

                                    {{-- Facilities --}}
                                    @if ($cinema->facilities)
                                        <div class="flex items-center gap-1">
                                            @foreach (array_slice($cinema->facilities ?? [], 0, 3) as $fac)
                                                @php
                                                    $facIcon = match ($fac) {
                                                        'parking' => 'ti-car',
                                                        'food_court' => 'ti-tools-kitchen',
                                                        'atm' => 'ti-building-bank',
                                                        'wifi' => 'ti-wifi',
                                                        'prayer_room' => 'ti-building-mosque',
                                                        'nursing_room' => 'ti-heart',
                                                        'handicap_access' => 'ti-accessible',
                                                        default => 'ti-star',
                                                    };
                                                @endphp
                                                <div class="w-6 h-6 bg-white/5 rounded-lg flex items-center
                            justify-center"
                                                    title="{{ str_replace('_', ' ', $fac) }}">
                                                    <i class="ti {{ $facIcon }} text-gray-600 text-[10px]"></i>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                            </a>
                        @endforeach
                    </div>

                </div>
            @endforeach

        @endif

    </div>

    @push('scripts')
        <script>
            function updateUrlParam(key, value) {
                const url = new URL(window.location.href);
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
                url.searchParams.delete('page');
                return url.toString();
            }
        </script>
    @endpush

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
