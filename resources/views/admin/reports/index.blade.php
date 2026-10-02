<x-app-layout>
    <x-slot name="header">Laporan Penjualan Bioskop</x-slot>

    <div class="p-6 space-y-6" x-data="reportPage()" x-init="init()">

        {{-- Filter Bar --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
            <form method="GET" id="filter-form" class="flex flex-wrap items-end gap-3">

                {{-- Period presets --}}
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                        Periode
                    </label>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ([
        'today' => 'Hari Ini',
        'yesterday' => 'Kemarin',
        'this_week' => 'Minggu Ini',
        'last_week' => 'Minggu Lalu',
        'this_month' => 'Bulan Ini',
        'last_month' => 'Bulan Lalu',
        'this_year' => 'Tahun Ini',
        'custom' => 'Kustom',
    ] as $val => $label)
                            <button type="button" onclick="setPeriod('{{ $val }}')"
                                class="h-8 px-3 text-xs font-semibold rounded-xl border
                            transition-all duration-150
                            {{ $period === $val
                                ? 'bg-gray-900 border-gray-900 text-white'
                                : 'border-gray-200 text-gray-600 hover:border-gray-400' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Custom date range --}}
                <div id="custom-range"
                    class="{{ $period === 'custom' ? 'flex' : 'hidden' }}
                    items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="bg-gray-50 border border-gray-200 text-gray-700 text-sm
                        rounded-xl px-3 h-10 outline-none focus:border-gray-400
                        transition-all duration-200">
                    <span class="text-gray-400 text-sm">–</span>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="bg-gray-50 border border-gray-200 text-gray-700 text-sm
                        rounded-xl px-3 h-10 outline-none focus:border-gray-400
                        transition-all duration-200">
                </div>

                <input type="hidden" name="period" id="period-input" value="{{ $period }}">

                {{-- Cinema filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                        Bioskop
                    </label>
                    <select name="cinema" onchange="this.form.submit()"
                        class="bg-gray-50 border border-gray-200 text-gray-700 text-sm
                        rounded-xl px-3 h-10 outline-none cursor-pointer
                        focus:border-gray-400 min-w-[160px]">
                        <option value="">Semua Bioskop</option>
                        @foreach ($cinemas as $c)
                            <option value="{{ $c->id }}" {{ $cinema == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Movie filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                        Film
                    </label>
                    <select name="movie" onchange="this.form.submit()"
                        class="bg-gray-50 border border-gray-200 text-gray-700 text-sm
                        rounded-xl px-3 h-10 outline-none cursor-pointer
                        focus:border-gray-400 min-w-[160px]">
                        <option value="">Semua Film</option>
                        @foreach ($movies as $m)
                            <option value="{{ $m->id }}" {{ $movie == $m->id ? 'selected' : '' }}>
                                {{ Str::limit($m->title, 30) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Apply & Export --}}
                <div class="flex gap-2">
                    <button type="submit"
                        class="h-10 px-5 bg-gray-900 hover:bg-gray-800 text-white
                        text-sm font-semibold rounded-xl transition-all duration-200
                        shadow-sm flex items-center gap-1.5">
                        <i class="ti ti-filter text-base"></i>
                        Terapkan
                    </button>
                    <a href="{{ route('admin.reports.export', array_merge(request()->all())) }}"
                        class="h-10 px-4 border border-gray-200 text-gray-600 text-sm
                        font-medium rounded-xl hover:bg-gray-50 transition-all
                        duration-200 flex items-center gap-1.5 shadow-sm">
                        <i class="ti ti-download text-base"></i>
                        CSV
                    </a>
                </div>

            </form>

            {{-- Date range info --}}
            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center
            justify-between">
                <p class="text-xs text-gray-500">
                    Menampilkan data:
                    <span class="font-semibold text-gray-700">
                        {{ $startDate->translatedFormat('d F Y') }}
                        @if ($startDate->ne($endDate))
                            — {{ $endDate->translatedFormat('d F Y') }}
                        @endif
                    </span>
                    <span class="text-gray-400 ml-1">
                        ({{ $startDate->diffInDays($endDate) + 1 }} hari)
                    </span>
                </p>
                @if ($cinema || $movie)
                    <a href="{{ route('admin.reports.index', ['period' => $period]) }}"
                        class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                        <i class="ti ti-x text-xs"></i>
                        Reset filter
                    </a>
                @endif
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-4">
            @foreach ([
        [
            'label' => 'Total Pendapatan',
            'value' => 'Rp ' . number_format($summary['total_revenue'], 0, ',', '.'),
            'sub' => $summary['total_revenue'] > 0 && $prevSummary['total_revenue'] > 0 ? $this->growthPct($summary['total_revenue'], $prevSummary['total_revenue']) : null,
            'icon' => 'ti-currency-dollar',
            'color' => 'text-emerald-600',
            'bg' => 'bg-emerald-50',
            'dark' => true,
        ],
        [
            'label' => 'Total Order',
            'value' => number_format($summary['total_orders']),
            'sub' => null,
            'icon' => 'ti-shopping-cart',
            'color' => 'text-blue-600',
            'bg' => 'bg-blue-50',
        ],
        [
            'label' => 'Tiket Terjual',
            'value' => number_format($summary['total_tickets']),
            'sub' => null,
            'icon' => 'ti-ticket',
            'color' => 'text-violet-600',
            'bg' => 'bg-violet-50',
        ],
        [
            'label' => 'Rata-rata Order',
            'value' => 'Rp ' . number_format($summary['avg_order_value'], 0, ',', '.'),
            'sub' => null,
            'icon' => 'ti-chart-line',
            'color' => 'text-amber-600',
            'bg' => 'bg-amber-50',
        ],
    ] as $stat)
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <div
                            class="w-9 h-9 {{ $stat['bg'] }} rounded-xl flex items-center
                    justify-center">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-lg"></i>
                        </div>
                    </div>
                    <p class="text-xl font-black text-gray-900">{{ $stat['value'] }}</p>
                    @if (isset($stat['sub']) && $stat['sub'])
                        @php $growth = $stat['sub']; @endphp
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span
                                class="text-xs font-semibold flex items-center gap-0.5
                    {{ $growth >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                <i class="ti {{ $growth >= 0 ? 'ti-trending-up' : 'ti-trending-down' }} text-sm"></i>
                                {{ abs($growth) }}%
                            </span>
                            <span class="text-[10px] text-gray-400">vs periode sebelumnya</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Secondary stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach ([['label' => 'Pelanggan Unik', 'value' => number_format($summary['unique_customers']), 'icon' => 'ti-users', 'color' => 'text-cyan-600'], ['label' => 'Total Jadwal', 'value' => number_format($summary['total_showtimes']), 'icon' => 'ti-calendar', 'color' => 'text-gray-600'], ['label' => 'Order Dibatalkan', 'value' => number_format($summary['cancelled_orders']), 'icon' => 'ti-x', 'color' => 'text-red-500'], ['label' => 'Order Pending', 'value' => number_format($summary['pending_orders']), 'icon' => 'ti-clock', 'color' => 'text-amber-500']] as $s)
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <i class="ti {{ $s['icon'] }} {{ $s['color'] }} text-xl flex-shrink-0"></i>
                        <div>
                            <p class="text-lg font-black text-gray-900">{{ $s['value'] }}</p>
                            <p class="text-[10px] text-gray-400">{{ $s['label'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Revenue Chart --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Tren Pendapatan</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $startDate->translatedFormat('d M Y') }}
                        — {{ $endDate->translatedFormat('d M Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-3 text-[10px]">
                    <span class="flex items-center gap-1.5 text-gray-500">
                        <span class="w-3 h-3 bg-gray-900 rounded-sm"></span>
                        Pendapatan
                    </span>
                    <span class="flex items-center gap-1.5 text-gray-400">
                        <span class="w-3 h-1 bg-blue-400 rounded-sm"></span>
                        Order
                    </span>
                </div>
            </div>

            {{-- Chart canvas --}}
            <div class="relative h-64">
                <canvas id="revenue-chart"></canvas>
            </div>
        </div>

        {{-- Top Movies + Top Cinemas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Top Movies --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">Film Terlaris</h2>
                </div>
                @if ($topMovies->isEmpty())
                    <div class="p-8 text-center">
                        <i class="ti ti-movie-off text-3xl text-gray-300 block mb-2"></i>
                        <p class="text-sm text-gray-400">Belum ada data</p>
                    </div>
                @else
                    @php $maxRevMovie = $topMovies->max('revenue'); @endphp
                    <div class="divide-y divide-gray-50">
                        @foreach ($topMovies as $i => $m)
                            <div class="flex items-center gap-4 px-5 py-3.5">

                                {{-- Rank --}}
                                <div class="w-6 flex-shrink-0 text-center">
                                    @if ($i < 3)
                                        <span
                                            class="text-sm font-black
                            {{ $i === 0 ? 'text-amber-500' : ($i === 1 ? 'text-gray-400' : 'text-amber-700') }}">
                                            {{ $i + 1 }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">{{ $i + 1 }}</span>
                                    @endif
                                </div>

                                {{-- Poster --}}
                                <div
                                    class="w-10 h-14 bg-gray-100 rounded-xl overflow-hidden
                        flex-shrink-0 border border-gray-200">
                                    @php $poster = $m->movie_poster; @endphp
                                    @if ($poster)
                                        <img src="{{ str_starts_with($poster, 'http') ? $poster : Storage::url($poster) }}"
                                            class="w-full h-full object-cover" alt="{{ $m->movie_title }}"
                                            loading="lazy">
                                    @else
                                        <div
                                            class="w-full h-full flex items-center justify-center
                            bg-gray-100">
                                            <i class="ti ti-movie text-gray-400 text-base"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info + bar --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate mb-1">
                                        {{ $m->movie_title }}
                                    </p>
                                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden mb-1">
                                        <div class="h-full bg-gray-800 rounded-full"
                                            style="width: {{ $maxRevMovie > 0 ? round(($m->revenue / $maxRevMovie) * 100) : 0 }}%">
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400">
                                        {{ number_format($m->tickets) }} tiket
                                        · {{ number_format($m->orders) }} order
                                    </p>
                                </div>

                                {{-- Revenue --}}
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm font-black text-gray-900">
                                        @if ($m->revenue >= 1000000)
                                            {{ number_format($m->revenue / 1000000, 1) }}jt
                                        @else
                                            {{ number_format($m->revenue / 1000, 0) }}rb
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Top Cinemas --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">Bioskop Terlaris</h2>
                </div>
                @if ($topCinemas->isEmpty())
                    <div class="p-8 text-center">
                        <i class="ti ti-building-off text-3xl text-gray-300 block mb-2"></i>
                        <p class="text-sm text-gray-400">Belum ada data</p>
                    </div>
                @else
                    @php $maxRevCinema = $topCinemas->max('revenue'); @endphp
                    <div class="divide-y divide-gray-50">
                        @foreach ($topCinemas as $i => $c)
                            <div class="flex items-center gap-4 px-5 py-3.5">

                                {{-- Rank --}}
                                <div class="w-6 flex-shrink-0 text-center">
                                    @if ($i < 3)
                                        <span
                                            class="text-sm font-black
                            {{ $i === 0 ? 'text-amber-500' : ($i === 1 ? 'text-gray-400' : 'text-amber-700') }}">
                                            {{ $i + 1 }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">{{ $i + 1 }}</span>
                                    @endif
                                </div>

                                {{-- Icon --}}
                                <div
                                    class="w-10 h-10 bg-gray-100 rounded-xl flex items-center
                        justify-center flex-shrink-0 border border-gray-200">
                                    <i class="ti ti-building text-gray-500 text-lg"></i>
                                </div>

                                {{-- Info + bar --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate mb-0.5">
                                        {{ $c->cinema_name }}
                                    </p>
                                    <p class="text-xs text-gray-400 mb-1">{{ $c->cinema_city }}</p>
                                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden mb-1">
                                        <div class="h-full bg-gray-800 rounded-full"
                                            style="width: {{ $maxRevCinema > 0 ? round(($c->revenue / $maxRevCinema) * 100) : 0 }}%">
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400">
                                        {{ number_format($c->tickets) }} tiket
                                        · {{ number_format($c->orders) }} order
                                    </p>
                                </div>

                                {{-- Revenue --}}
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm font-black text-gray-900">
                                        @if ($c->revenue >= 1000000)
                                            {{ number_format($c->revenue / 1000000, 1) }}jt
                                        @else
                                            {{ number_format($c->revenue / 1000, 0) }}rb
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Format & Ticket Type Breakdown --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- By Format --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-4">
                    Pendapatan per Format
                </h3>
                @if ($revenueByFormat->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada data</p>
                @else
                    @php $maxFmt = $revenueByFormat->max('revenue'); @endphp
                    <div class="space-y-3">
                        @foreach ($revenueByFormat as $fmt)
                            @php
                                $fmtColors = [
                                    'imax' => 'bg-blue-500',
                                    '4dx' => 'bg-purple-500',
                                    '3d' => 'bg-cyan-500',
                                    'imax_3d' => 'bg-indigo-500',
                                    'dolby' => 'bg-rose-500',
                                    '2d' => 'bg-gray-600',
                                ];
                                $barColor = $fmtColors[$fmt->format] ?? 'bg-gray-500';
                                $pctFmt = $maxFmt > 0 ? round(($fmt->revenue / $maxFmt) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-semibold text-gray-700 uppercase">
                                        {{ $fmt->format }}
                                    </span>
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <span>{{ number_format($fmt->tickets) }} tiket</span>
                                        <span class="font-bold text-gray-900">
                                            Rp {{ number_format($fmt->revenue, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="{{ $barColor }} h-full rounded-full
                            transition-all duration-700"
                                        style="width: {{ $pctFmt }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- By Ticket Type --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-4">
                    Pendapatan per Tipe Tiket
                </h3>
                @if ($revenueByTicketType->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada data</p>
                @else
                    @php $maxTkt = $revenueByTicketType->max('revenue'); @endphp
                    <div class="space-y-3">
                        @foreach ($revenueByTicketType as $tkt)
                            @php
                                $tktColors = [
                                    'regular' => 'bg-emerald-500',
                                    'student' => 'bg-blue-500',
                                    'senior' => 'bg-amber-500',
                                    'vip' => 'bg-violet-500',
                                ];
                                $tktLabels = [
                                    'regular' => 'Regular',
                                    'student' => 'Pelajar/Mahasiswa',
                                    'senior' => 'Lansia',
                                    'vip' => 'VIP / Couple',
                                ];
                                $barColorTkt = $tktColors[$tkt->ticket_type] ?? 'bg-gray-500';
                                $pctTkt = $maxTkt > 0 ? round(($tkt->revenue / $maxTkt) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-semibold text-gray-700">
                                        {{ $tktLabels[$tkt->ticket_type] ?? ucfirst($tkt->ticket_type) }}
                                    </span>
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <span>{{ number_format($tkt->tickets) }} tiket</span>
                                        <span class="font-bold text-gray-900">
                                            Rp {{ number_format($tkt->revenue, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="{{ $barColorTkt }} h-full rounded-full
                            transition-all duration-700"
                                        style="width: {{ $pctTkt }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Daily Transactions Table --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">
                    Ringkasan Harian
                    <span class="text-gray-400 font-normal text-xs ml-1">
                        (30 hari terbaru)
                    </span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            @foreach (['Tanggal', 'Order Berhasil', 'Dibatalkan', 'Pelanggan', 'Pendapatan'] as $i => $th)
                                <th
                                    class="px-5 py-3 text-xs font-semibold text-gray-500
                            uppercase tracking-wider
                            {{ $i === 0 ? 'text-left' : 'text-right' }}">
                                    {{ $th }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($dailyTransactions as $day)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($day->date)->translatedFormat('D, d M Y') }}
                                    @if (\Carbon\Carbon::parse($day->date)->isToday())
                                        <span class="text-[10px] font-bold text-blue-500 ml-1">
                                            Hari ini
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="text-sm font-semibold text-emerald-600">
                                        {{ number_format($day->paid_orders) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span
                                        class="text-sm
                                {{ $day->cancelled_orders > 0 ? 'font-semibold text-red-500' : 'text-gray-300' }}">
                                        {{ number_format($day->cancelled_orders) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-sm text-gray-600">
                                    {{ number_format($day->customers) }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="text-sm font-black text-gray-900">
                                        Rp {{ number_format($day->revenue, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center">
                                    <p class="text-sm text-gray-400">Belum ada data transaksi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($dailyTransactions->count() > 0)
                        <tfoot class="bg-gray-50/70 border-t border-gray-200">
                            <tr>
                                <td class="px-5 py-3 text-xs font-bold text-gray-600">
                                    TOTAL
                                </td>
                                <td
                                    class="px-5 py-3 text-right text-sm font-black
                            text-emerald-600">
                                    {{ number_format($dailyTransactions->sum('paid_orders')) }}
                                </td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-red-500">
                                    {{ number_format($dailyTransactions->sum('cancelled_orders')) }}
                                </td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-gray-700">
                                    {{ number_format($dailyTransactions->sum('customers')) }}
                                </td>
                                <td class="px-5 py-3 text-right text-base font-black text-gray-900">
                                    Rp {{ number_format($dailyTransactions->sum('revenue'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>

    @push('scripts')
        {{-- Chart.js --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

        <script>
            // Chart
            const TREND_DATA = @json($revenueTrend);

            function reportPage() {
                return {
                    init() {
                        this.$nextTick(() => this.initChart());
                    },

                    initChart() {
                        const ctx = document.getElementById('revenue-chart');
                        if (!ctx) return;

                        const labels = TREND_DATA.map(d => d.label);
                        const revenues = TREND_DATA.map(d => d.revenue);
                        const orders = TREND_DATA.map(d => d.orders);

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                        label: 'Pendapatan (Rp)',
                                        data: revenues,
                                        backgroundColor: '#111827',
                                        borderRadius: 6,
                                        borderSkipped: false,
                                        yAxisID: 'y',
                                    },
                                    {
                                        label: 'Order',
                                        data: orders,
                                        type: 'line',
                                        borderColor: '#60a5fa',
                                        backgroundColor: 'rgba(96,165,250,0.1)',
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBackgroundColor: '#60a5fa',
                                        tension: 0.3,
                                        fill: true,
                                        yAxisID: 'y2',
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: '#1f2937',
                                        titleColor: '#f9fafb',
                                        bodyColor: '#d1d5db',
                                        borderColor: '#374151',
                                        borderWidth: 1,
                                        padding: 10,
                                        callbacks: {
                                            label: ctx => {
                                                if (ctx.datasetIndex === 0) {
                                                    return ' Rp ' + ctx.raw.toLocaleString('id-ID');
                                                }
                                                return ' ' + ctx.raw + ' order';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            color: '#9ca3af',
                                            font: {
                                                size: 10
                                            },
                                            maxRotation: 45,
                                        },
                                    },
                                    y: {
                                        position: 'left',
                                        grid: {
                                            color: '#f3f4f6'
                                        },
                                        ticks: {
                                            color: '#9ca3af',
                                            font: {
                                                size: 10
                                            },
                                            callback: v => {
                                                if (v >= 1000000)
                                                    return 'Rp ' + (v / 1000000).toFixed(1) + 'jt';
                                                if (v >= 1000)
                                                    return 'Rp ' + (v / 1000).toFixed(0) + 'rb';
                                                return 'Rp ' + v;
                                            }
                                        },
                                    },
                                    y2: {
                                        position: 'right',
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            color: '#93c5fd',
                                            font: {
                                                size: 10
                                            },
                                        },
                                    },
                                },
                            },
                        });
                    },
                }
            }

            // Period & filter helpers
            function setPeriod(val) {
                document.getElementById('period-input').value = val;

                const customEl = document.getElementById('custom-range');
                if (val === 'custom') {
                    customEl.classList.remove('hidden');
                    customEl.classList.add('flex');
                } else {
                    customEl.classList.add('hidden');
                    customEl.classList.remove('flex');
                    document.getElementById('filter-form').submit();
                }

                // Highlight active button
                document.querySelectorAll('[onclick^="setPeriod"]').forEach(btn => {
                    const isActive = btn.getAttribute('onclick') === `setPeriod('${val}')`;
                    btn.classList.toggle('bg-gray-900', isActive);
                    btn.classList.toggle('border-gray-900', isActive);
                    btn.classList.toggle('text-white', isActive);
                    btn.classList.toggle('border-gray-200', !isActive);
                    btn.classList.toggle('text-gray-600', !isActive);
                });
            }
        </script>
    @endpush

</x-app-layout>
