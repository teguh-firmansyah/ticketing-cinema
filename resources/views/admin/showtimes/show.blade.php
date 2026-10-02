<x-app-layout>
    <x-slot name="header">Detail Jadwal</x-slot>

    <div class="p-6 space-y-6">

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

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <a href="{{ route('admin.showtimes.index') }}"
                    class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                    justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                    transition-all duration-200 shadow-sm flex-shrink-0 mt-0.5">
                    <i class="ti ti-arrow-left text-base"></i>
                </a>

                <div class="flex items-start gap-4">
                    <div
                        class="w-16 h-22 bg-gray-100 rounded-2xl overflow-hidden
                    flex-shrink-0 border border-gray-200 shadow-sm">
                        <img src="{{ $showtime->movie->poster_url }}" class="w-full h-full object-cover"
                            alt="{{ $showtime->movie->title }}">
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-gray-900 mb-0.5">
                            {{ $showtime->movie->title }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            @php
                                $fmtCls =
                                    [
                                        'imax' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        '4dx' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        '3d' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                        'imax_3d' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'dolby' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        '2d' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    ][$showtime->format] ?? 'bg-gray-100 text-gray-600 border-gray-200';

                                $statusCfg =
                                    [
                                        'open' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'full' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                                        'ended' => 'bg-gray-100 text-gray-500 border-gray-200',
                                    ][$showtime->status] ?? 'bg-gray-100 text-gray-500 border-gray-200';
                            @endphp
                            <span
                                class="text-xs font-bold px-2.5 py-1 rounded-full border
                            {{ $fmtCls }}">
                                {{ $showtime->format_label }}
                            </span>
                            <span
                                class="text-xs font-semibold px-2.5 py-1 rounded-full border
                            {{ $statusCfg }}">
                                {{ $showtime->status_label }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $showtime->language_label }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 flex flex-wrap items-center gap-2">
                            <span class="font-bold text-gray-900 text-base">
                                {{ $showtime->start_time->format('H:i') }}
                                <span class="text-gray-400 font-normal text-sm">–</span>
                                {{ $showtime->end_time->format('H:i') }}
                            </span>
                            <span class="text-gray-300">·</span>
                            {{ $showtime->start_time->translatedFormat('l, d F Y') }}
                            @if ($showtime->start_time->isToday())
                                <span class="text-blue-500 font-semibold text-xs">Hari ini</span>
                            @endif
                            <span class="text-gray-300">·</span>
                            <a href="{{ route('admin.cinema.show', $showtime->studio->cinema) }}"
                                class="hover:underline">
                                {{ $showtime->studio->cinema->name }}
                            </a>
                            <span class="text-gray-300">·</span>
                            {{ $showtime->studio->name }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                {{-- Change status --}}
                @if ($showtime->status !== 'cancelled' && $showtime->status !== 'ended')
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="h-9 px-3 border border-gray-200 text-gray-500 text-xs
                        font-medium rounded-xl hover:bg-gray-50 transition-all
                        duration-200 flex items-center gap-1.5">
                            <i class="ti ti-adjustments text-sm"></i>
                            Ubah Status
                        </button>
                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="absolute right-0 top-full mt-2 w-44 bg-white
                        border border-gray-200 rounded-xl shadow-lg z-10 py-1
                        overflow-hidden">
                            @foreach ([['open', 'Buka', 'emerald'], ['full', 'Penuh', 'amber'], ['cancelled', 'Batalkan', 'red'], ['ended', 'Selesai', 'gray']] as [$val, $label, $c])
                                @if ($val !== $showtime->status)
                                    <form method="POST" action="{{ route('admin.showtimes.status', $showtime) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $val }}">
                                        <button type="submit" @click="open = false"
                                            class="w-full text-left px-4 py-2.5 text-sm font-medium
                                hover:bg-gray-50 transition text-{{ $c }}-600
                                flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-{{ $c }}-500"></span>
                                            {{ $label }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($showtime->status !== 'cancelled')
                    <a href="{{ route('admin.showtimes.edit', $showtime) }}"
                        class="h-9 px-4 bg-gray-900 hover:bg-gray-800 text-white text-xs
                    font-semibold rounded-xl transition-all duration-200
                    flex items-center gap-1.5 shadow-sm">
                        <i class="ti ti-edit text-sm"></i>
                        Edit
                    </a>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ([['label' => 'Total Kursi', 'value' => $seatStats['total'], 'color' => 'text-gray-900'], ['label' => 'Tersedia', 'value' => $seatStats['available'], 'color' => 'text-emerald-600'], ['label' => 'Terkunci', 'value' => $seatStats['locked'], 'color' => 'text-amber-600'], ['label' => 'Terisi', 'value' => $seatStats['booked'], 'color' => 'text-red-600']] as $s)
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <p class="text-xs text-gray-500 mb-1">{{ $s['label'] }}</p>
                    <p class="text-2xl font-black {{ $s['color'] }}">{{ $s['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Occupancy bar --}}
        @php
            $total = $seatStats['total'] ?: 1;
            $pctBooked = round(($seatStats['booked'] / $total) * 100);
            $pctLocked = round(($seatStats['locked'] / $total) * 100);
        @endphp
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-900">Okupansi Kursi</p>
                <p class="text-sm text-gray-500">
                    {{ $pctBooked + $pctLocked }}% terisi
                </p>
            </div>
            <div class="h-3 bg-gray-100 rounded-full overflow-hidden flex">
                <div class="bg-red-500 h-full transition-all duration-500" style="width: {{ $pctBooked }}%"></div>
                <div class="bg-amber-400 h-full transition-all duration-500" style="width: {{ $pctLocked }}%"></div>
            </div>
            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                    Booked ({{ $seatStats['booked'] }})
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 bg-amber-400 rounded-full"></span>
                    Locked ({{ $seatStats['locked'] }})
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 bg-gray-200 rounded-full"></span>
                    Available ({{ $seatStats['available'] }})
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Detail info --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-xs font-semibold text-gray-500 uppercase
                tracking-wider mb-4">Detail
                    Jadwal</h3>
                <div class="space-y-3">
                    @foreach ([['Bioskop', $showtime->studio->cinema->name], ['Studio', $showtime->studio->name . ' (' . $showtime->studio->type_name . ')'], ['Format', $showtime->format_label], ['Bahasa', $showtime->language_label], ['Mulai', $showtime->start_time->translatedFormat('D, d M Y — H:i')], ['Selesai', $showtime->end_time->translatedFormat('D, d M Y — H:i')], ['Durasi', $showtime->start_time->diffForHumans($showtime->end_time, true)]] as [$label, $val])
                        <div class="flex justify-between text-sm gap-2">
                            <span class="text-gray-500 flex-shrink-0">{{ $label }}</span>
                            <span class="font-medium text-gray-800 text-right">{{ $val }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Harga --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-xs font-semibold text-gray-500 uppercase
                tracking-wider mb-4">Harga
                    Tiket</h3>
                <div class="space-y-3">
                    @foreach ([['Regular', $showtime->price_regular, 'text-gray-900'], ['Pelajar', $showtime->price_student, 'text-gray-700'], ['Lansia', $showtime->price_senior, 'text-gray-700'], ['VIP / Couple', $showtime->price_vip, 'text-violet-700']] as [$label, $price, $cls])
                        @if ($price > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">{{ $label }}</span>
                                <span class="text-sm font-bold {{ $cls }}">
                                    Rp {{ number_format($price, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Pendapatan</span>
                        <span class="text-base font-black text-emerald-600">
                            Rp {{ number_format($revenue, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if ($showtime->notes)
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase
                tracking-wider mb-3">
                        Catatan</h3>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        {{ $showtime->notes }}
                    </p>
                </div>
            @endif

        </div>

        {{-- Orders --}}
        @if ($orders->count() > 0)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">
                        Order Terkini
                        <span class="text-gray-400 font-normal text-xs ml-1">
                            ({{ $orders->count() }} ditampilkan)
                        </span>
                    </h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ($orders as $order)
                        <div class="flex items-center gap-4 px-5 py-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $order->user->name }}
                                    </p>
                                    @php
                                        $oBadge = [
                                            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        ];
                                    @endphp
                                    <span
                                        class="text-xs font-medium px-2 py-0.5 rounded-full border
                            {{ $oBadge[$order->status] ?? '' }}">
                                        {{ $order->status_label }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5 font-mono">
                                    {{ $order->order_number }}
                                    · {{ $order->created_at->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-gray-900">
                                    Rp {{ number_format($order->total, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $order->seats_string }}
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
                    <p class="text-sm font-medium text-gray-700">Hapus Jadwal</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Jadwal tidak dapat dihapus jika ada tiket yang sudah terbayar.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.showtimes.destroy', $showtime) }}"
                    onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-9 px-4 bg-red-50
                        border border-red-200 text-red-600 text-xs font-semibold
                        rounded-xl hover:bg-red-100 transition-all duration-200">
                        <i class="ti ti-trash text-sm"></i>
                        Hapus Jadwal
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
