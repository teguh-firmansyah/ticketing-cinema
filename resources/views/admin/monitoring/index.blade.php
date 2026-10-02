<x-app-layout>
    <x-slot name="header">Live Monitoring Bioskop</x-slot>

    <div class="p-6 space-y-6" x-data="monitoring()" x-init="init()">

        {{-- Header + Controls --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center
        justify-between gap-4">

            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-emerald-500
                animate-pulse flex-shrink-0"></div>
                <div>
                    <h1 class="text-xl font-black text-gray-900">Live Monitoring</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Auto-refresh setiap 10 detik ·
                        <span x-text="'Update terakhir: ' + lastUpdate"></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                {{-- Date picker --}}
                <input type="date" id="date-picker" value="{{ $date }}" onchange="updateDate(this.value)"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm
                    focus:border-gray-400">

                {{-- Cinema filter --}}
                <select id="cinema-filter" onchange="updateCinema(this.value)"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm
                    min-w-[160px]">
                    <option value="">Semua Bioskop</option>
                    @foreach ($cinemas as $c)
                        <option value="{{ $c->id }}" {{ $cinema == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->city }})
                        </option>
                    @endforeach
                </select>

                {{-- Refresh toggle --}}
                <button @click="toggleRefresh()"
                    :class="autoRefresh
                        ?
                        'bg-emerald-50 border-emerald-200 text-emerald-700' :
                        'bg-gray-100 border-gray-200 text-gray-500'"
                    class="h-10 px-4 border text-xs font-semibold rounded-xl
                    transition-all duration-200 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full"
                        :class="autoRefresh
                            ?
                            'bg-emerald-500 animate-pulse' :
                            'bg-gray-400'">
                    </span>
                    <span x-text="autoRefresh ? 'Live' : 'Paused'"></span>
                </button>

                {{-- Manual refresh --}}
                <button @click="fetchData()" :disabled="loading"
                    class="h-10 px-3 bg-white border border-gray-200 text-gray-500
                    text-sm rounded-xl hover:bg-gray-50 transition-all duration-200
                    flex items-center gap-1.5 shadow-sm">
                    <i class="ti ti-refresh text-base" :class="loading ? 'animate-spin' : ''"></i>
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach ([['key' => 'paid_orders', 'label' => 'Order Lunas', 'icon' => 'ti-circle-check', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'], ['key' => 'pending_orders', 'label' => 'Menunggu Bayar', 'icon' => 'ti-clock', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'], ['key' => 'tickets_sold', 'label' => 'Tiket Terjual', 'icon' => 'ti-ticket', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'], ['key' => 'active_shows', 'label' => 'Tayang Sekarang', 'icon' => 'ti-player-play', 'color' => 'text-red-600', 'bg' => 'bg-red-50'], ['key' => 'upcoming_shows', 'label' => 'Jadwal Berikut', 'icon' => 'ti-calendar-event', 'color' => 'text-violet-600', 'bg' => 'bg-violet-50'], ['key' => 'total_orders', 'label' => 'Total Order', 'icon' => 'ti-shopping-cart', 'color' => 'text-gray-600', 'bg' => 'bg-gray-100']] as $s)
                <div
                    class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm
            lg:col-span-1 col-span-1">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[10px] font-medium text-gray-500 leading-tight">
                            {{ $s['label'] }}
                        </p>
                        <div
                            class="w-7 h-7 {{ $s['bg'] }} rounded-xl flex items-center
                    justify-center flex-shrink-0">
                            <i class="ti {{ $s['icon'] }} {{ $s['color'] }} text-sm"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900"
                        x-text="stats.{{ $s['key'] }} ?? '{{ $stats[$s['key']] }}'">
                        {{ $stats[$s['key']] }}
                    </p>
                </div>
            @endforeach

            {{-- Revenue card (wider) --}}
            <div
                class="bg-gray-900 border border-gray-800 rounded-2xl p-4 shadow-sm
            col-span-2 sm:col-span-4 lg:col-span-1">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[10px] font-medium text-gray-400">Pendapatan</p>
                    <div class="w-7 h-7 bg-white/10 rounded-xl flex items-center
                    justify-center">
                        <i class="ti ti-currency-dollar text-white text-sm"></i>
                    </div>
                </div>
                <p class="text-lg font-black text-white"
                    x-text="formatRupiah(stats.revenue ?? {{ $stats['revenue'] }})">
                    Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Showtime Monitor --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">
                        Jadwal Tayang
                        <span class="text-gray-400 font-normal text-xs ml-1"
                            x-text="'(' + showtimes.length + ' jadwal)'">
                            ({{ $showtimes->count() }} jadwal)
                        </span>
                    </h2>
                    <div class="flex items-center gap-3 text-[10px] text-gray-500">
                        @foreach ([['bg-emerald-500', 'Tersedia'], ['bg-amber-500', 'Terkunci'], ['bg-red-500', 'Terisi']] as [$bg, $label])
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full {{ $bg }}"></span>
                                {{ $label }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Showtime cards --}}
                <div class="space-y-3" id="showtime-list">

                    {{-- Server-rendered initial data --}}
                    @forelse($showtimes as $st)
                        @php
                            $total = $st->studio->total_seats ?: 1;
                            $booked = \App\Models\ShowtimeSeat::where('showtime_id', $st->id)
                                ->where('status', 'booked')
                                ->count();
                            $locked = \App\Models\ShowtimeSeat::where('showtime_id', $st->id)
                                ->where('status', 'locked')
                                ->where('locked_until', '>', now())
                                ->count();
                            $available = max(0, $total - $booked - $locked);
                            $pct = round(($booked / $total) * 100);
                            $isNow = $st->start_time->lt(now()) && $st->end_time->gt(now());
                        @endphp
                        <div class="bg-white border rounded-2xl overflow-hidden shadow-sm
                    transition-all duration-300
                    {{ $isNow ? 'border-red-200 ring-1 ring-red-100' : 'border-gray-100' }}"
                            data-showtime="{{ $st->id }}">

                            <div class="flex items-start gap-4 p-4">

                                {{-- Now playing indicator --}}
                                @if ($isNow)
                                    <div class="absolute top-4 right-4">
                                        <span
                                            class="text-[10px] font-bold bg-red-500 text-white
                                px-2 py-0.5 rounded-full flex items-center gap-1">
                                            <span class="w-1 h-1 bg-white rounded-full animate-pulse"></span>
                                            LIVE
                                        </span>
                                    </div>
                                @endif

                                {{-- Poster --}}
                                <div
                                    class="w-12 h-16 bg-gray-100 rounded-xl overflow-hidden
                            flex-shrink-0 border border-gray-200">
                                    <img src="{{ $st->movie->poster_url }}" class="w-full h-full object-cover"
                                        alt="{{ $st->movie->title }}" loading="lazy">
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 truncate">
                                                {{ $st->movie->title }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ $st->studio->cinema->name }}
                                                · {{ $st->studio->name }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <span class="text-xs font-bold text-gray-900">
                                                {{ $st->start_time->format('H:i') }}
                                            </span>
                                            <span class="text-gray-300 text-xs">–</span>
                                            <span class="text-xs text-gray-500">
                                                {{ $st->end_time->format('H:i') }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Progress bar --}}
                                    <div class="mt-3 space-y-1.5">
                                        <div
                                            class="flex items-center justify-between text-[10px]
                                    text-gray-500">
                                            <span>Okupansi kursi</span>
                                            <span class="font-bold text-gray-700">
                                                {{ $booked }}/{{ $total }}
                                                ({{ $pct }}%)
                                            </span>
                                        </div>
                                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden flex">
                                            <div class="h-full bg-red-500 transition-all duration-500"
                                                style="width: {{ $pct }}%"></div>
                                            <div class="h-full bg-amber-400 transition-all duration-500"
                                                style="width: {{ round(($locked / $total) * 100) }}%"></div>
                                        </div>
                                        <div class="flex items-center gap-3 text-[10px] text-gray-500">
                                            <span class="text-emerald-600 font-medium">
                                                {{ $available }} tersedia
                                            </span>
                                            <span class="text-amber-600">{{ $locked }} terkunci</span>
                                            <span class="text-red-600">{{ $booked }} terisi</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Seat map button --}}
                                <button @click="openSeatMap({{ $st->id }})"
                                    class="flex-shrink-0 w-9 h-9 bg-gray-100 hover:bg-gray-200
                                rounded-xl flex items-center justify-center text-gray-500
                                hover:text-gray-700 transition-all duration-150"
                                    title="Lihat denah kursi">
                                    <i class="ti ti-layout-grid text-base"></i>
                                </button>

                            </div>

                            {{-- Format & lang bar --}}
                            <div class="px-4 pb-3 flex items-center gap-2">
                                <span
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full border
                            bg-gray-50 border-gray-200 text-gray-500">
                                    {{ $st->format_label }}
                                </span>
                                <span class="text-[10px] text-gray-400">
                                    {{ $st->language_label }}
                                </span>
                                <span class="text-[10px] text-gray-300">·</span>
                                <span class="text-[10px] text-gray-400">
                                    Rp {{ number_format($st->price_regular, 0, ',', '.') }}
                                </span>
                                @if ($isNow)
                                    <div class="ml-auto">
                                        <span
                                            class="text-[10px] font-bold text-red-500
                                flex items-center gap-1">
                                            <span
                                                class="w-1.5 h-1.5 bg-red-500 rounded-full
                                    animate-pulse"></span>
                                            Sedang Tayang
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div
                            class="text-center py-16 bg-white border border-gray-100
                    rounded-2xl shadow-sm">
                            <i class="ti ti-calendar-off text-5xl text-gray-300 block mb-3"></i>
                            <p class="text-sm font-semibold text-gray-400 mb-1">
                                Tidak ada jadwal
                            </p>
                            <p class="text-xs text-gray-300">
                                Pada tanggal {{ $selectedDate->translatedFormat('d F Y') }}
                            </p>
                        </div>
                    @endforelse

                </div>
            </div>

            {{-- Live Feed --}}
            <div class="space-y-5">

                {{-- Recent Transactions --}}
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            Transaksi Terbaru
                        </h2>
                        <span class="text-[10px] text-gray-400"
                            x-text="serverTime">{{ now()->format('H:i:s') }}</span>
                    </div>

                    <div class="divide-y divide-gray-50 max-h-[420px] overflow-y-auto" id="recent-orders">
                        @forelse($recentOrders as $order)
                            <div
                                class="flex items-start gap-3 px-4 py-3
                        hover:bg-gray-50/50 transition-colors">
                                <div
                                    class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center
                            justify-center text-xs font-black
                            {{ $order->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ strtoupper(substr($order->user->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <p class="text-xs font-semibold text-gray-900 truncate">
                                            {{ $order->user->name }}
                                        </p>
                                        <span
                                            class="text-[10px] font-bold flex-shrink-0
                                    {{ $order->status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $order->status === 'paid' ? 'Lunas' : 'Pending' }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-gray-500 truncate mt-0.5">
                                        {{ $order->showtime->movie->title }}
                                        · {{ $order->seats_string }}
                                    </p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-[10px] text-gray-400">
                                            {{ $order->created_at->format('H:i:s') }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-700">
                                            Rp {{ number_format($order->total, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <i class="ti ti-shopping-cart-off text-3xl text-gray-300 block mb-2"></i>
                                <p class="text-xs text-gray-400">Belum ada transaksi</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Revenue by Movie --}}
                @if ($revenueByMovie->count() > 0)
                    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900">
                                Pendapatan per Film
                            </h2>
                        </div>
                        <div class="p-4 space-y-3">
                            @php $maxRevenue = $revenueByMovie->max('total_revenue'); @endphp
                            @foreach ($revenueByMovie as $item)
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-gray-700 truncate max-w-[60%]">
                                            {{ $item->movie_title }}
                                        </span>
                                        <span class="font-bold text-gray-900 flex-shrink-0">
                                            Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        @php
                                            $pct =
                                                $maxRevenue > 0 ? round(($item->total_revenue / $maxRevenue) * 100) : 0;
                                        @endphp
                                        <div class="h-full bg-gray-800 rounded-full
                                transition-all duration-500"
                                            style="width: {{ $pct }}%">
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400">
                                        {{ $item->total_tickets }} tiket
                                        · {{ $item->total_orders }} order
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- Seat Map Modal --}}
        <div x-show="seatMapOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            @click.self="seatMapOpen = false"
            class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center
            justify-center p-4">

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh]
            flex flex-col overflow-hidden"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                {{-- Modal header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <p class="text-sm font-bold text-gray-900" x-text="seatMapData?.showtime?.movie_title">
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5"
                            x-text="seatMapData
                            ? (seatMapData.showtime.cinema_name + ' · '
                               + seatMapData.showtime.studio_name + ' · '
                               + seatMapData.showtime.start_time + '–'
                               + seatMapData.showtime.end_time)
                            : ''">
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Stats --}}
                        <div class="flex items-center gap-3 text-xs" x-show="seatMapData">
                            <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                <span class="w-2 h-2 bg-emerald-500 rounded-sm"></span>
                                <span x-text="seatMapData?.stats?.available + ' tersedia'"></span>
                            </span>
                            <span class="text-amber-600 font-semibold flex items-center gap-1">
                                <span class="w-2 h-2 bg-amber-500 rounded-sm"></span>
                                <span x-text="seatMapData?.stats?.locked + ' terkunci'"></span>
                            </span>
                            <span class="text-red-600 font-semibold flex items-center gap-1">
                                <span class="w-2 h-2 bg-red-500 rounded-sm"></span>
                                <span x-text="seatMapData?.stats?.booked + ' terisi'"></span>
                            </span>
                        </div>
                        <button @click="seatMapOpen = false"
                            class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl
                            flex items-center justify-center text-gray-500
                            transition-all duration-150">
                            <i class="ti ti-x text-base"></i>
                        </button>
                    </div>
                </div>

                {{-- Loading state --}}
                <div x-show="seatMapLoading" class="flex-1 flex items-center justify-center py-20">
                    <div class="text-center">
                        <svg class="animate-spin w-8 h-8 text-gray-400 mx-auto mb-3" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-sm text-gray-400">Memuat denah kursi...</p>
                    </div>
                </div>

                {{-- Seat map content --}}
                <div x-show="!seatMapLoading && seatMapData" class="flex-1 overflow-y-auto p-6">

                    {{-- Screen --}}
                    <div class="text-center mb-5 max-w-xs mx-auto">
                        <div
                            class="h-1.5 bg-gradient-to-r from-transparent
                        via-gray-300 to-transparent rounded-full">
                        </div>
                        <p class="text-[9px] text-gray-400 tracking-widest mt-1 uppercase">
                            Layar
                        </p>
                    </div>

                    {{-- Legend --}}
                    <div
                        class="flex items-center justify-center gap-4 mb-4 text-[10px]
                    text-gray-500">
                        @foreach ([['bg-emerald-200 border-emerald-300', 'Tersedia'], ['bg-amber-200 border-amber-300', 'Terkunci'], ['bg-red-400 border-red-500', 'Terisi'], ['bg-gray-200 border-gray-300', 'Nonaktif']] as [$cls, $label])
                            <span class="flex items-center gap-1.5">
                                <span class="w-4 h-4 border rounded-[3px] {{ $cls }}"></span>
                                {{ $label }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Grid --}}
                    <div class="overflow-x-auto">
                        <div class="space-y-1.5 min-w-max mx-auto">
                            <template x-for="(cols, row) in seatMapData?.seats" :key="row">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="text-[10px] text-gray-400 w-5 text-right
                                    flex-shrink-0 font-medium"
                                        x-text="row"></span>
                                    <div class="flex gap-1">
                                        <template x-for="seat in cols" :key="seat.seat_number">
                                            <div x-tooltip="seat.seat_number + ' — ' + seat.status
                                            + (seat.locked_by ? ' (dikunci: ' + seat.locked_by + ')' : '')"
                                                class="w-6 h-6 rounded-[3px] border
                                                transition-all duration-200
                                                cursor-default flex items-center
                                                justify-center text-[8px] font-bold"
                                                :class="{
                                                    'bg-emerald-100 border-emerald-300 text-emerald-700': seat
                                                        .status === 'available',
                                                    'bg-amber-300 border-amber-400 text-amber-900': seat
                                                        .status === 'locked',
                                                    'bg-red-400 border-red-500 text-white': seat.status === 'booked',
                                                    'bg-gray-100 border-gray-200 text-gray-300': seat
                                                        .status === 'disabled',
                                                }"
                                                :title="seat.seat_number + ' — ' + seat.status +
                                                    (seat.locked_by ?
                                                        '\nDikunci oleh: ' + seat.locked_by +
                                                        '\nHingga: ' + seat.locked_until :
                                                        '')">
                                                <span x-show="seat.status === 'booked'" class="text-[8px]">✓</span>
                                                <span x-show="seat.status === 'locked'" class="text-[8px]">⏳</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Refresh seat map --}}
                <div class="px-6 py-3 border-t border-gray-100 flex items-center
                justify-between">
                    <p class="text-xs text-gray-400">
                        Klik seat untuk melihat detail
                    </p>
                    <button @click="loadSeatMap(currentShowtimeId)"
                        class="text-xs text-gray-500 hover:text-gray-700 flex items-center
                        gap-1.5 transition">
                        <i class="ti ti-refresh text-sm"></i>
                        Refresh
                    </button>
                </div>

            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function monitoring() {
                return {
                    // State
                    autoRefresh: true,
                    loading: false,
                    lastUpdate: '--:--:--',
                    serverTime: '--:--:--',
                    refreshInterval: null,
                    showtimes: [],
                    stats: @json($stats),
                    recentOrders: [],

                    // Seat map
                    seatMapOpen: false,
                    seatMapLoading: false,
                    seatMapData: null,
                    currentShowtimeId: null,

                    // Config
                    date: '{{ $date }}',
                    cinema: '{{ $cinema }}',

                    // Init
                    init() {
                        this.startAutoRefresh();
                    },

                    // Auto refresh
                    startAutoRefresh() {
                        this.refreshInterval = setInterval(() => {
                            if (this.autoRefresh) this.fetchData();
                        }, 10000);
                    },

                    toggleRefresh() {
                        this.autoRefresh = !this.autoRefresh;
                    },

                    // Fetch real-time data
                    async fetchData() {
                        if (this.loading) return;
                        this.loading = true;

                        try {
                            const params = new URLSearchParams({
                                date: this.date,
                                cinema: this.cinema,
                            });

                            const res = await fetch(
                                `{{ route('admin.monitoring.realtime') }}?${params}`, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                }
                            );
                            const data = await res.json();

                            this.stats = data.stats;
                            this.serverTime = data.server_time;
                            this.lastUpdate = data.server_time;
                            this.recentOrders = data.recent_orders;

                            // Update showtime cards
                            this.updateShowtimeCards(data.showtimes);

                            // Update recent orders feed
                            this.updateOrderFeed(data.recent_orders);

                        } catch (err) {
                            console.error('Monitoring fetch error:', err);
                        } finally {
                            this.loading = false;
                        }
                    },

                    // Update showtime DOM
                    updateShowtimeCards(showtimes) {
                        showtimes.forEach(st => {
                            const card = document.querySelector(
                                `[data-showtime="${st.id}"]`
                            );
                            if (!card) return;

                            const total = st.total || 1;
                            const pct = Math.round((st.booked / total) * 100);
                            const lPct = Math.round((st.locked / total) * 100);

                            // Update progress bars
                            const bars = card.querySelectorAll('.h-2 > div');
                            if (bars[0]) bars[0].style.width = pct + '%';
                            if (bars[1]) bars[1].style.width = lPct + '%';

                            // Update counts
                            const countEls = card.querySelectorAll('[data-stat]');
                            countEls.forEach(el => {
                                const key = el.dataset.stat;
                                if (st[key] !== undefined) {
                                    el.textContent = st[key];
                                }
                            });
                        });
                    },

                    // Update order feed
                    updateOrderFeed(orders) {
                        const container = document.getElementById('recent-orders');
                        if (!container || !orders.length) return;

                        container.innerHTML = orders.map(o => `
                <div class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50/50 transition-colors">
                    <div class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center
                        justify-center text-xs font-black
                        ${o.status === 'paid'
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-amber-50 text-amber-700'}">
                        ${o.user_name.substring(0,2).toUpperCase()}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <p class="text-xs font-semibold text-gray-900 truncate">
                                ${o.user_name}
                            </p>
                            <span class="text-[10px] font-bold flex-shrink-0
                                ${o.status === 'paid' ? 'text-emerald-600' : 'text-amber-600'}">
                                ${o.status === 'paid' ? 'Lunas' : 'Pending'}
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-500 truncate mt-0.5">
                            ${o.movie_title} · ${o.seats}
                        </p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-[10px] text-gray-400">${o.created_at}</span>
                            <span class="text-[10px] font-bold text-gray-700">
                                Rp ${o.total.toLocaleString('id-ID')}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
                    },

                    // Seat map
                    async openSeatMap(showtimeId) {
                        this.seatMapOpen = true;
                        this.seatMapData = null;
                        this.currentShowtimeId = showtimeId;
                        await this.loadSeatMap(showtimeId);
                    },

                    async loadSeatMap(showtimeId) {
                        this.seatMapLoading = true;

                        try {
                            const res = await fetch(
                                `{{ url('admin/monitoring/showtime') }}/${showtimeId}/seats`, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                }
                            );
                            this.seatMapData = await res.json();
                        } catch (err) {
                            console.error('Seat map error:', err);
                        } finally {
                            this.seatMapLoading = false;
                        }
                    },

                    // Helpers
                    formatRupiah(amount) {
                        return 'Rp ' + (amount || 0).toLocaleString('id-ID');
                    },
                }
            }

            // URL helpers
            function updateDate(val) {
                const url = new URL(window.location.href);
                url.searchParams.set('date', val);
                window.location.href = url.toString();
            }

            function updateCinema(val) {
                const url = new URL(window.location.href);
                if (val) {
                    url.searchParams.set('cinema', val);
                } else {
                    url.searchParams.delete('cinema');
                }
                window.location.href = url.toString();
            }
        </script>
    @endpush

</x-app-layout>
