<x-app-layout>
    <x-slot name="header">Detail Film</x-slot>

    <div class="p-6 space-y-6">

        {{-- Flash --}}
        @foreach (['success' => 'emerald', 'error' => 'red'] as $type => $color)
            @if (session($type))
                <div class="flex items-center gap-3 bg-{{ $color }}-50 border
        border-{{ $color }}-200 text-{{ $color }}-700 text-sm px-4 py-3 rounded-xl"
                    x-data x-init="setTimeout(() => $el.remove(), 4000)">
                    <i
                        class="ti ti-{{ $type === 'success' ? 'circle-check' : 'alert-circle' }}
            flex-shrink-0"></i>
                    {{ session($type) }}
                </div>
            @endif
        @endforeach

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <a href="{{ route('admin.movies.index') }}"
                    class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                    justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                    transition-all duration-200 shadow-sm flex-shrink-0 mt-0.5">
                    <i class="ti ti-arrow-left text-base"></i>
                </a>
                <div class="flex items-start gap-4">
                    {{-- Poster --}}
                    <div
                        class="w-20 h-28 bg-gray-100 rounded-2xl overflow-hidden
                    flex-shrink-0 border border-gray-200 shadow-sm">
                        <img src="{{ $movie->poster_url }}" class="w-full h-full object-cover"
                            alt="{{ $movie->title }}">
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h1 class="text-xl font-black text-gray-900">
                                {{ $movie->title }}
                            </h1>
                            @php
                                $sBadge = [
                                    'now_showing' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'coming_soon' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'ended' => 'bg-gray-100 text-gray-500 border-gray-200',
                                ];
                            @endphp
                            <span
                                class="text-xs font-semibold px-2.5 py-1 rounded-full border
                            {{ $sBadge[$movie->status] ?? '' }}">
                                {{ $movie->status_label }}
                            </span>
                            @if ($movie->is_featured)
                                <span
                                    class="text-xs font-semibold px-2.5 py-1 rounded-full
                            bg-amber-50 border border-amber-200 text-amber-700
                            flex items-center gap-1">
                                    <i class="ti ti-star-filled text-xs"></i>Featured
                                </span>
                            @endif
                        </div>
                        @if ($movie->original_title !== $movie->title)
                            <p class="text-sm text-gray-400 italic mb-1">
                                {{ $movie->original_title }}
                            </p>
                        @endif
                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                            @if ($movie->director)
                                <span class="flex items-center gap-1.5">
                                    <i class="ti ti-movie text-xs text-gray-400"></i>
                                    {{ $movie->director }}
                                </span>
                            @endif
                            @if ($movie->duration)
                                <span class="flex items-center gap-1.5">
                                    <i class="ti ti-clock text-xs text-gray-400"></i>
                                    {{ $movie->duration_formatted }}
                                </span>
                            @endif
                            @if ($movie->vote_average)
                                <span class="flex items-center gap-1.5 text-amber-500">
                                    <i class="ti ti-star-filled text-xs"></i>
                                    {{ $movie->vote_average }}/10
                                </span>
                            @endif
                            @if ($movie->age_rating)
                                <span
                                    class="text-xs font-bold px-2 py-0.5 rounded border
                            border-gray-300 text-gray-600">
                                    {{ $movie->age_rating }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach ($movie->genres ?? [] as $genre)
                                <span
                                    class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1
                            rounded-full border border-gray-200">
                                    {{ $genre }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                @if ($movie->tmdb_id)
                    <form method="POST" action="{{ route('admin.movies.sync', $movie) }}">
                        @csrf
                        <button type="submit"
                            class="h-9 px-3 border border-gray-200 text-gray-500 text-xs
                        font-medium rounded-xl hover:bg-blue-50 hover:text-blue-600
                        hover:border-blue-200 transition-all duration-200
                        flex items-center gap-1.5">
                            <i class="ti ti-refresh text-sm"></i>
                            Sync TMDb
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.movies.featured', $movie) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="h-9 px-3 border text-xs font-medium rounded-xl
                        transition-all duration-200 flex items-center gap-1.5
                        {{ $movie->is_featured
                            ? 'border-amber-200 text-amber-600 bg-amber-50 hover:bg-amber-100'
                            : 'border-gray-200 text-gray-500 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200' }}">
                        <i class="ti ti-star{{ $movie->is_featured ? '-filled' : '' }} text-sm"></i>
                        {{ $movie->is_featured ? 'Unfeature' : 'Feature' }}
                    </button>
                </form>
                <a href="{{ route('admin.movies.edit', $movie) }}"
                    class="h-9 px-4 bg-gray-900 hover:bg-gray-800 text-white text-xs
                    font-semibold rounded-xl transition-all duration-200
                    flex items-center gap-1.5 shadow-sm">
                    <i class="ti ti-edit text-sm"></i>Edit
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4">
            @foreach ([['label' => 'Total Jadwal', 'value' => $showtimeStats['total'], 'icon' => 'ti-calendar', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'], ['label' => 'Jadwal Mendatang', 'value' => $showtimeStats['upcoming'], 'icon' => 'ti-clock', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'], ['label' => 'Tiket Terjual', 'value' => $showtimeStats['tickets'], 'icon' => 'ti-ticket', 'color' => 'text-violet-600', 'bg' => 'bg-violet-50']] as $stat)
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                        <div
                            class="w-8 h-8 {{ $stat['bg'] }} rounded-xl flex items-center
                    justify-center">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-base"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Detail grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Sinopsis --}}
            <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Sinopsis
                </h3>
                <p class="text-sm text-gray-700 leading-relaxed">
                    {{ $movie->synopsis ?: 'Sinopsis belum tersedia.' }}
                </p>

                @if ($movie->cast && count($movie->cast) > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p
                            class="text-xs font-semibold text-gray-500 uppercase
                    tracking-wider mb-2">
                            Pemeran</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($movie->cast as $actor)
                                <span
                                    class="text-xs bg-gray-50 border border-gray-200 text-gray-600
                        px-2.5 py-1 rounded-full">{{ $actor }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar info --}}
            <div class="space-y-4">
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase
                    tracking-wider mb-4">
                        Info</h3>
                    <div class="space-y-3">
                        @foreach ([['Rilis', $movie->release_date?->translatedFormat('d M Y') ?? '-'], ['Durasi', $movie->duration_formatted], ['Bahasa', strtoupper($movie->language ?? '-')], ['Rating', $movie->age_rating], ['TMDb ID', $movie->tmdb_id ?? '-'], ['Vote', $movie->vote_average ? $movie->vote_average . '/10 (' . number_format($movie->vote_count) . ')' : '-']] as [$label, $val])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ $label }}</span>
                                <span class="font-medium text-gray-800 text-right">
                                    {{ $val }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($movie->trailer_url)
                    <a href="{{ $movie->trailer_url }}" target="_blank"
                        class="flex items-center gap-3 bg-red-50 border border-red-100
                    rounded-2xl p-4 hover:bg-red-100 transition-colors duration-200">
                        <div
                            class="w-9 h-9 bg-red-500 rounded-xl flex items-center
                    justify-center flex-shrink-0">
                            <i class="ti ti-brand-youtube text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-red-700">Tonton Trailer</p>
                            <p class="text-xs text-red-400">Buka di YouTube</p>
                        </div>
                        <i class="ti ti-external-link text-red-400 ml-auto text-sm"></i>
                    </a>
                @endif
            </div>

        </div>

        {{-- Showtimes --}}
        @if ($showtimes->count() > 0)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">Jadwal Mendatang</h2>
                    {{-- <a href="{{ route('admin.showtimes.index', ['movie' => $movie->id]) }}"
                class="text-xs text-gray-500 hover:text-gray-700">
                Lihat semua →
            </a> --}}
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ($showtimes as $st)
                        <div class="flex items-center gap-4 px-5 py-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $st->studio->cinema->name }}
                                    <span class="text-gray-400 font-normal">·</span>
                                    {{ $st->studio->name }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $st->start_time->translatedFormat('D, d M Y — H:i') }}
                                    · {{ $st->format_label }} · {{ $st->language_label }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs font-semibold text-gray-700">
                                    {{ $st->available_seats }} tersedia
                                </p>
                                <p class="text-xs text-gray-400">
                                    Mulai Rp {{ number_format($st->price_regular, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Danger zone --}}
        <div class="bg-white border border-red-100 rounded-2xl p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-red-600 mb-3 flex items-center gap-2">
                <i class="ti ti-alert-triangle text-base"></i>
                Zona Berbahaya
            </h3>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">Hapus Film</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Film tidak dapat dihapus jika masih memiliki jadwal aktif.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.movies.destroy', $movie) }}"
                    onsubmit="return confirm('Hapus film \"{{ $movie->title }}\"?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-9 px-4 bg-red-50
                        border border-red-200 text-red-600 text-xs font-semibold
                        rounded-xl hover:bg-red-100 transition-all duration-200">
                        <i class="ti ti-trash text-sm"></i>
                        Hapus Film
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
