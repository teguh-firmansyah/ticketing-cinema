<x-app-layout>
    <x-slot name="header">Jadwal Tayang</x-slot>

    <div class="p-6 space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([['label' => 'Total Jadwal', 'value' => $stats['total'], 'icon' => 'ti-calendar', 'color' => 'text-gray-700', 'bg' => 'bg-gray-100'], ['label' => 'Hari Ini', 'value' => $stats['today'], 'icon' => 'ti-calendar-event', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'], ['label' => 'Aktif', 'value' => $stats['open'], 'icon' => 'ti-player-play', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'], ['label' => 'Penuh', 'value' => $stats['full'], 'icon' => 'ti-armchair', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50']] as $s)
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-500">{{ $s['label'] }}</p>
                        <div class="w-9 h-9 {{ $s['bg'] }} rounded-xl flex items-center justify-center">
                            <i class="ti {{ $s['icon'] }} {{ $s['color'] }} text-lg"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $s['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Flash --}}
        @foreach (['success' => 'emerald', 'error' => 'red'] as $type => $color)
            @if (session($type))
                <div class="flex items-center gap-3 bg-{{ $color }}-50 border
        border-{{ $color }}-200 text-{{ $color }}-700 text-sm px-4 py-3 rounded-xl"
                    x-data x-init="setTimeout(() => $el.remove(), 4000)">
                    <i class="ti ti-{{ $type === 'success' ? 'circle-check' : 'alert-circle' }} flex-shrink-0"></i>
                    {{ session($type) }}
                </div>
            @endif
        @endforeach

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center
        justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2 flex-1">

                <div
                    class="flex items-center gap-2 bg-white border border-gray-200
                rounded-xl px-3 h-10 w-48 focus-within:border-gray-400
                transition-colors shadow-sm">
                    <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Judul film..."
                        class="bg-transparent border-none outline-none text-sm
                        text-gray-700 placeholder-gray-400 w-full">
                </div>

                <input type="date" name="date" value="{{ $date }}"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm
                    focus:border-gray-400">

                <select name="cinema" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm
                    min-w-[150px]">
                    <option value="">Semua Bioskop</option>
                    @foreach ($cinemas as $c)
                        <option value="{{ $c->id }}" {{ $cinema == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>

                <select name="movie" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm
                    min-w-[150px]">
                    <option value="">Semua Film</option>
                    @foreach ($movies as $m)
                        <option value="{{ $m->id }}" {{ $movie == $m->id ? 'selected' : '' }}>
                            {{ Str::limit($m->title, 30) }}
                        </option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm">
                    <option value="">Semua Status</option>
                    @foreach (['open' => 'Open', 'full' => 'Penuh', 'cancelled' => 'Dibatalkan', 'ended' => 'Selesai'] as $val => $label)
                        <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @if ($search || $cinema || $movie || $status || $date)
                    <a href="{{ route('admin.showtimes.index') }}"
                        class="h-10 px-3 border border-gray-200 text-gray-500 text-sm
                    rounded-xl hover:bg-gray-50 flex items-center gap-1.5
                    transition-all duration-200">
                        <i class="ti ti-x text-sm"></i>Reset
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('admin.showtimes.create', ['bulk' => 1]) }}"
                    class="inline-flex items-center gap-2 h-10 px-4 border border-gray-200
                    text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-50
                    transition-all duration-200 shadow-sm">
                    <i class="ti ti-copy text-base"></i>
                    Bulk Input
                </a>
                <a href="{{ route('admin.showtimes.create') }}"
                    class="inline-flex items-center gap-2 h-10 px-5 bg-gray-900
                    hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                    transition-all duration-200 shadow-sm">
                    <i class="ti ti-plus text-base"></i>
                    Tambah Jadwal
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            @foreach (['Film', 'Bioskop & Studio', 'Jadwal', 'Format', 'Harga', 'Kursi', 'Status', 'Aksi'] as $i => $th)
                                <th
                                    class="px-4 py-3.5 text-xs font-semibold text-gray-500
                            uppercase tracking-wider
                            {{ $i >= 6 ? 'text-center' : 'text-left' }}
                            {{ $i === 7 ? 'text-right' : '' }}">
                                    {{ $th }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($showtimes as $st)
                            @php
                                $isPast = $st->start_time->lt(now());
                                $statusCfg = [
                                    'open' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Buka'],
                                    'full' => ['bg-amber-50 text-amber-700 border-amber-200', 'Penuh'],
                                    'cancelled' => ['bg-red-50 text-red-600 border-red-200', 'Batal'],
                                    'ended' => ['bg-gray-100 text-gray-500 border-gray-200', 'Selesai'],
                                ];
                                [$sCls, $sLabel] = $statusCfg[$st->status] ?? [
                                    'bg-gray-100 text-gray-500 border-gray-200',
                                    $st->status,
                                ];
                            @endphp
                            <tr
                                class="hover:bg-gray-50/50 transition-colors group
                        {{ $isPast ? 'opacity-60' : '' }}">

                                {{-- Film --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-14 bg-gray-100 rounded-xl overflow-hidden
                                    flex-shrink-0 border border-gray-200">
                                            <img src="{{ $st->movie->poster_url }}" class="w-full h-full object-cover"
                                                alt="{{ $st->movie->title }}" loading="lazy">
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-sm font-semibold text-gray-900
                                        truncate max-w-[140px]">
                                                {{ $st->movie->title }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                {{ $st->movie->duration_formatted }}
                                                · {{ $st->movie->age_rating }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Bioskop --}}
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $st->studio->cinema->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $st->studio->name }}
                                        <span class="text-gray-300">·</span>
                                        {{ $st->studio->cinema->city }}
                                    </p>
                                </td>

                                {{-- Jadwal --}}
                                <td class="px-4 py-3">
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $st->start_time->format('H:i') }}
                                        <span class="text-gray-400 font-normal text-xs">–</span>
                                        {{ $st->end_time->format('H:i') }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $st->start_time->translatedFormat('D, d M Y') }}
                                        @if ($st->start_time->isToday())
                                            <span class="text-blue-500 font-semibold ml-1">Hari ini</span>
                                        @endif
                                    </p>
                                </td>

                                {{-- Format --}}
                                <td class="px-4 py-3">
                                    @php
                                        $fmtCls =
                                            [
                                                'imax' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                '4dx' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                '3d' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                                'imax_3d' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                'dolby' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                '2d' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            ][$st->format] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                                    @endphp
                                    <span
                                        class="text-xs font-bold px-2 py-1 rounded-lg border
                                {{ $fmtCls }}">
                                        {{ $st->format_label }}
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $st->language_label }}
                                    </p>
                                </td>

                                {{-- Harga --}}
                                <td class="px-4 py-3">
                                    <p class="text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($st->price_regular, 0, ',', '.') }}
                                    </p>
                                    @if ($st->price_vip > 0)
                                        <p class="text-xs text-violet-600 mt-0.5">
                                            VIP: Rp {{ number_format($st->price_vip, 0, ',', '.') }}
                                        </p>
                                    @endif
                                </td>

                                {{-- Kursi --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <div
                                            class="flex-1 h-1.5 bg-gray-100 rounded-full
                                    overflow-hidden w-16">
                                            @php
                                                $total = $st->studio->total_seats ?: 1;
                                                $pct = round(($st->booked_seats / $total) * 100);
                                            @endphp
                                            <div class="h-full rounded-full transition-all
                                        {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 60 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500 whitespace-nowrap">
                                            {{ $st->available_seats }}/{{ $total }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="text-xs font-semibold px-2.5 py-1 rounded-full
                                border {{ $sCls }}">
                                        {{ $sLabel }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3">
                                    <div
                                        class="flex items-center justify-end gap-1
                                opacity-0 group-hover:opacity-100
                                transition-opacity duration-150">
                                        <a href="{{ route('admin.showtimes.show', $st) }}"
                                            class="w-8 h-8 bg-gray-100 hover:bg-blue-50
                                        hover:text-blue-600 rounded-lg flex items-center
                                        justify-center text-gray-500
                                        transition-all duration-150">
                                            <i class="ti ti-eye text-sm"></i>
                                        </a>
                                        @if (!$isPast && $st->status !== 'cancelled')
                                            <a href="{{ route('admin.showtimes.edit', $st) }}"
                                                class="w-8 h-8 bg-gray-100 hover:bg-amber-50
                                        hover:text-amber-600 rounded-lg flex items-center
                                        justify-center text-gray-500
                                        transition-all duration-150">
                                                <i class="ti ti-edit text-sm"></i>
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.showtimes.destroy', $st) }}"
                                            onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 bg-gray-100 hover:bg-red-50
                                            hover:text-red-600 rounded-lg flex items-center
                                            justify-center text-gray-500
                                            transition-all duration-150">
                                                <i class="ti ti-trash text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <i class="ti ti-calendar-off text-4xl text-gray-300 block mb-3"></i>
                                    <p class="text-sm text-gray-400 mb-4">
                                        Belum ada jadwal tayang
                                    </p>
                                    <a href="{{ route('admin.showtimes.create') }}"
                                        class="inline-flex items-center gap-2 h-9 px-4
                                    bg-gray-900 text-white text-xs font-semibold
                                    rounded-xl hover:bg-gray-800 transition-all">
                                        <i class="ti ti-plus text-sm"></i>
                                        Tambah Jadwal
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($showtimes->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        {{ $showtimes->firstItem() }}–{{ $showtimes->lastItem() }}
                        dari {{ $showtimes->total() }} jadwal
                    </p>
                    <div class="flex items-center gap-1.5">
                        @if (!$showtimes->onFirstPage())
                            <a href="{{ $showtimes->previousPageUrl() }}"
                                class="w-8 h-8 flex items-center justify-center border border-gray-200
                        rounded-lg text-gray-500 hover:bg-gray-50 transition text-sm">
                                <i class="ti ti-chevron-left"></i>
                            </a>
                        @endif
                        @foreach ($showtimes->getUrlRange(max(1, $showtimes->currentPage() - 2), min($showtimes->lastPage(), $showtimes->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="w-8 h-8 flex items-center justify-center border rounded-lg
                        text-xs font-medium transition
                        {{ $page === $showtimes->currentPage()
                            ? 'bg-gray-900 border-gray-900 text-white'
                            : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                                {{ $page }}
                            </a>
                        @endforeach
                        @if ($showtimes->hasMorePages())
                            <a href="{{ $showtimes->nextPageUrl() }}"
                                class="w-8 h-8 flex items-center justify-center border border-gray-200
                        rounded-lg text-gray-500 hover:bg-gray-50 transition text-sm">
                                <i class="ti ti-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
