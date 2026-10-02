<x-app-layout>
    <x-slot name="header">Kelola Film</x-slot>

    <div class="p-6 space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([['label' => 'Total Film', 'value' => $stats['total'], 'icon' => 'ti-movie', 'color' => 'text-gray-700', 'bg' => 'bg-gray-100'], ['label' => 'Sedang Tayang', 'value' => $stats['now_showing'], 'icon' => 'ti-player-play', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'], ['label' => 'Segera Hadir', 'value' => $stats['coming_soon'], 'icon' => 'ti-clock', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'], ['label' => 'Selesai', 'value' => $stats['ended'], 'icon' => 'ti-player-stop', 'color' => 'text-gray-400', 'bg' => 'bg-gray-100']] as $stat)
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <div class="w-9 h-9 {{ $stat['bg'] }} rounded-xl flex items-center justify-center">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-lg"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Flash --}}
        @foreach (['success' => 'emerald', 'error' => 'red'] as $type => $color)
            @if (session($type))
                <div class="flex items-center gap-3 bg-{{ $color }}-50 border border-{{ $color }}-200
        text-{{ $color }}-700 text-sm px-4 py-3 rounded-xl"
                    x-data x-init="setTimeout(() => $el.remove(), 4000)">
                    <i class="ti ti-{{ $type === 'success' ? 'circle-check' : 'alert-circle' }} flex-shrink-0"></i>
                    {{ session($type) }}
                </div>
            @endif
        @endforeach

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2 flex-1">
                <div
                    class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl
                px-3 h-10 w-56 focus-within:border-gray-400 transition-colors shadow-sm">
                    <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari judul, sutradara..."
                        class="bg-transparent border-none outline-none text-sm
                        text-gray-700 placeholder-gray-400 w-full">
                </div>

                <select name="status" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm">
                    <option value="">Semua Status</option>
                    <option value="now_showing" {{ $status === 'now_showing' ? 'selected' : '' }}>Sedang Tayang
                    </option>
                    <option value="coming_soon" {{ $status === 'coming_soon' ? 'selected' : '' }}>Segera Hadir</option>
                    <option value="ended" {{ $status === 'ended' ? 'selected' : '' }}>Selesai</option>
                </select>

                <select name="genre" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm">
                    <option value="">Semua Genre</option>
                    @foreach ($genres as $g)
                        <option value="{{ $g }}" {{ $genre === $g ? 'selected' : '' }}>{{ $g }}
                        </option>
                    @endforeach
                </select>

                @if ($search || $status || $genre)
                    <a href="{{ route('admin.movies.index') }}"
                        class="h-10 px-3 border border-gray-200 text-gray-500 text-sm rounded-xl
                    hover:bg-gray-50 flex items-center gap-1.5 transition-all duration-200">
                        <i class="ti ti-x text-sm"></i>Reset
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.movies.create') }}"
                class="inline-flex items-center gap-2 h-10 px-5 bg-gray-900
                hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                transition-all duration-200 shadow-sm flex-shrink-0">
                <i class="ti ti-plus text-base"></i>
                Tambah Film
            </a>
        </div>

        {{-- Movie Grid --}}
        @if ($movies->isEmpty())
            <div class="text-center py-20 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <i class="ti ti-movie-off text-5xl text-gray-300 block mb-3"></i>
                <p class="text-sm font-semibold text-gray-400 mb-1">Belum ada film</p>
                <a href="{{ route('admin.movies.create') }}"
                    class="inline-flex items-center gap-2 mt-4 h-10 px-5 bg-gray-900
                text-white text-sm font-semibold rounded-xl hover:bg-gray-800
                transition-all duration-200">
                    <i class="ti ti-plus text-base"></i>Tambah Film
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach ($movies as $movie)
                    <div
                        class="group bg-white border border-gray-100 rounded-2xl overflow-hidden
            shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">

                        {{-- Poster --}}
                        <a href="{{ route('admin.movies.show', $movie) }}"
                            class="block relative aspect-[2/3] bg-gray-100 overflow-hidden">
                            <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105
                        transition-transform duration-500">

                            {{-- Status badge --}}
                            @php
                                $sBadge = [
                                    'now_showing' => 'bg-emerald-500 text-white',
                                    'coming_soon' => 'bg-amber-500 text-white',
                                    'ended' => 'bg-gray-500 text-white',
                                ];
                            @endphp
                            <div class="absolute top-2 left-2">
                                <span
                                    class="text-[9px] font-bold px-2 py-0.5 rounded-full
                        {{ $sBadge[$movie->status] ?? 'bg-gray-400 text-white' }}">
                                    {{ $movie->status_label }}
                                </span>
                            </div>

                            @if ($movie->is_featured)
                                <div class="absolute top-2 right-2">
                                    <span
                                        class="w-6 h-6 bg-amber-400 rounded-full flex items-center
                        justify-center shadow">
                                        <i class="ti ti-star-filled text-white text-xs"></i>
                                    </span>
                                </div>
                            @endif

                            {{-- Actions overlay --}}
                            <div
                                class="absolute inset-0 bg-black/60 opacity-0
                    group-hover:opacity-100 transition-opacity duration-200
                    flex flex-col items-center justify-center gap-2 p-3">
                                <a href="{{ route('admin.movies.show', $movie) }}"
                                    class="w-full h-8 bg-white text-gray-900 text-xs font-bold
                            rounded-lg flex items-center justify-center gap-1.5
                            hover:bg-gray-100 transition">
                                    <i class="ti ti-eye text-sm"></i>Detail
                                </a>
                                <a href="{{ route('admin.movies.edit', $movie) }}"
                                    class="w-full h-8 bg-white/20 text-white text-xs font-medium
                            rounded-lg flex items-center justify-center gap-1.5
                            hover:bg-white/30 transition border border-white/30">
                                    <i class="ti ti-edit text-sm"></i>Edit
                                </a>
                            </div>
                        </a>

                        {{-- Info --}}
                        <div class="p-3">
                            <p class="text-xs font-bold text-gray-900 line-clamp-2 leading-snug mb-1">
                                {{ $movie->title }}
                            </p>
                            <div class="flex items-center justify-between">
                                @if ($movie->vote_average)
                                    <span class="flex items-center gap-0.5 text-[10px] text-amber-500 font-semibold">
                                        <i class="ti ti-star-filled text-[10px]"></i>
                                        {{ $movie->vote_average }}
                                    </span>
                                @endif
                                @if ($movie->total_showtimes > 0)
                                    <span class="text-[10px] text-gray-400">
                                        {{ $movie->total_showtimes }} jadwal
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($movies->hasPages())
                <div class="flex justify-center gap-1.5">
                    @if ($movies->onFirstPage())
                        <span
                            class="w-9 h-9 flex items-center justify-center border border-gray-200
            rounded-xl text-gray-300 cursor-not-allowed text-sm">
                            <i class="ti ti-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ $movies->previousPageUrl() }}"
                            class="w-9 h-9 flex items-center justify-center border border-gray-200
                rounded-xl text-gray-500 hover:bg-gray-50 transition text-sm">
                            <i class="ti ti-chevron-left"></i>
                        </a>
                    @endif
                    @foreach ($movies->getUrlRange(max(1, $movies->currentPage() - 2), min($movies->lastPage(), $movies->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}"
                            class="w-9 h-9 flex items-center justify-center border rounded-xl
                text-xs font-medium transition
                {{ $page === $movies->currentPage()
                    ? 'bg-gray-900 border-gray-900 text-white'
                    : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                            {{ $page }}
                        </a>
                    @endforeach
                    @if ($movies->hasMorePages())
                        <a href="{{ $movies->nextPageUrl() }}"
                            class="w-9 h-9 flex items-center justify-center border border-gray-200
                rounded-xl text-gray-500 hover:bg-gray-50 transition text-sm">
                            <i class="ti ti-chevron-right"></i>
                        </a>
                    @endif
                </div>
            @endif
        @endif

    </div>
</x-app-layout>
