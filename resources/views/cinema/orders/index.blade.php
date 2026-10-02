<x-cinema-layout>
    <x-slot name="title">Pesanan Saya — {{ setting('app_name') }} Cinema</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-black text-white">Pesanan Saya</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Riwayat semua pembelian tiket bioskop
                </p>
            </div>
            <a href="{{ route('cinema.movies') }}"
                class="inline-flex items-center gap-2 h-10 px-4 bg-red-600
                hover:bg-red-500 text-white text-sm font-semibold rounded-xl
                transition-all duration-200">
                <i class="ti ti-ticket text-base"></i>
                Beli Tiket
            </a>
        </div>

        @if (session('success'))
            <div class="mb-5 flex items-center gap-3 bg-emerald-500/10 border
        border-emerald-500/20 text-emerald-400 text-sm px-4 py-3 rounded-xl"
                x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <i class="ti ti-circle-check text-base flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            @foreach ([['label' => 'Total Order', 'value' => $stats['total'], 'color' => 'text-white', 'icon' => 'ti-shopping-cart', 'bg' => 'bg-gray-800'], ['label' => 'Pending', 'value' => $stats['pending'], 'color' => 'text-amber-400', 'icon' => 'ti-clock', 'bg' => 'bg-amber-500/10'], ['label' => 'Berhasil', 'value' => $stats['paid'], 'color' => 'text-emerald-400', 'icon' => 'ti-circle-check', 'bg' => 'bg-emerald-500/10'], ['label' => 'Total Belanja', 'value' => 'Rp ' . ($stats['spent'] >= 1000000 ? number_format($stats['spent'] / 1000000, 1) . 'jt' : number_format($stats['spent'] / 1000, 0) . 'rb'), 'color' => 'text-red-400', 'icon' => 'ti-currency-dollar', 'bg' => 'bg-red-500/10']] as $stat)
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-600">{{ $stat['label'] }}</p>
                        <div
                            class="w-8 h-8 {{ $stat['bg'] }} rounded-xl flex items-center
                    justify-center">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-sm"></i>
                        </div>
                    </div>
                    <p class="text-xl font-black {{ $stat['color'] }}">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Filter + Search --}}
        <div class="bg-gray-900 border border-white/5 rounded-2xl p-4 mb-5">
            <form method="GET" class="flex flex-wrap gap-3 items-center">
                {{-- Search --}}
                <div
                    class="flex items-center gap-2 bg-white/5 border border-white/10
                rounded-xl px-3 h-9 flex-1 min-w-[200px] focus-within:border-white/20
                transition-all duration-200">
                    <i class="ti ti-search text-gray-600 text-sm"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nomor order atau judul film..."
                        class="bg-transparent border-none outline-none text-sm text-white
                        placeholder-gray-600 w-full">
                    @if ($search)
                        <a href="{{ route('cinema.my-orders', array_merge(request()->except('search'))) }}"
                            class="text-gray-600 hover:text-white transition flex-shrink-0">
                            <i class="ti ti-x text-xs"></i>
                        </a>
                    @endif
                </div>

                {{-- Status filter --}}
                <div class="flex gap-1.5 flex-wrap">
                    @foreach ([
        '' => ['label' => 'Semua', 'active' => 'bg-white/10 text-white border-white/20'],
        'pending' => ['label' => 'Pending', 'active' => 'bg-amber-500/20 text-amber-400 border-amber-500/30'],
        'paid' => ['label' => 'Berhasil', 'active' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'],
        'cancelled' => ['label' => 'Batal', 'active' => 'bg-gray-700 text-gray-400 border-gray-600'],
        'expired' => ['label' => 'Expired', 'active' => 'bg-gray-700 text-gray-500 border-gray-600'],
    ] as $val => $cfg)
                        <a href="{{ route('cinema.my-orders', array_merge(request()->except('status', 'page'), $val ? ['status' => $val] : [])) }}"
                            class="h-9 px-3 text-xs font-medium border rounded-xl
                        transition-all duration-200 flex items-center
                        {{ $status === $val
                            ? $cfg['active']
                            : 'bg-white/5 border-white/10 text-gray-500 hover:text-white hover:border-white/20' }}">
                            {{ $cfg['label'] }}
                        </a>
                    @endforeach
                </div>

                <button type="submit"
                    class="h-9 px-4 bg-gray-800 border border-white/10 text-gray-400
                    text-sm rounded-xl hover:text-white hover:border-white/20
                    transition-all duration-200 hidden">
                    Cari
                </button>
            </form>
        </div>

        {{-- Orders list --}}
        @if ($orders->isEmpty())
            <div class="text-center py-20 bg-gray-900/50 border border-white/5 rounded-2xl">
                <i class="ti ti-ticket-off text-5xl text-gray-700 block mb-4"></i>
                <p class="text-base font-semibold text-gray-400 mb-2">
                    Belum ada pesanan
                </p>
                <p class="text-sm text-gray-600 mb-6">
                    @if ($search || $status)
                        Tidak ada pesanan yang sesuai dengan filter
                    @else
                        Yuk beli tiket bioskop pertamamu!
                    @endif
                </p>
                <a href="{{ route('cinema.movies') }}"
                    class="inline-flex items-center gap-2 h-11 px-6 bg-red-600
                hover:bg-red-500 text-white text-sm font-semibold rounded-xl
                transition-all duration-200">
                    <i class="ti ti-movie text-base"></i>
                    Lihat Film Sekarang
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($orders as $order)
                    <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden
            hover:border-white/10 transition-all duration-200 group"
                        x-data="{ cancelOpen: false }">

                        {{-- Main clickable area --}}
                        <a href="{{ route('cinema.orders.show', $order->id) }}" class="block">
                            <div class="flex items-start gap-4 p-5">

                                {{-- Poster --}}
                                <div
                                    class="w-14 h-20 bg-gray-800 rounded-xl overflow-hidden
                        flex-shrink-0 border border-white/5">
                                    <img src="{{ $order->showtime->movie->poster_url }}"
                                        alt="{{ $order->showtime->movie->title }}" loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105
                                transition-transform duration-300">
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="min-w-0">
                                            <h3
                                                class="text-sm font-bold text-white truncate
                                    group-hover:text-red-400 transition-colors duration-200">
                                                {{ $order->showtime->movie->title }}
                                            </h3>
                                            <p class="text-xs font-mono text-gray-600 mt-0.5">
                                                {{ $order->order_number }}
                                            </p>
                                        </div>

                                        {{-- Status badge --}}
                                        @php
                                            $statusCfg = [
                                                'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'paid' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                                'cancelled' => 'bg-gray-700/50 text-gray-500 border-white/5',
                                                'expired' => 'bg-gray-700/30 text-gray-600 border-white/5',
                                                'refunded' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            ];
                                        @endphp
                                        <span
                                            class="flex-shrink-0 text-xs font-semibold px-2.5 py-1
                                rounded-xl border {{ $statusCfg[$order->status] ?? '' }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </div>

                                    {{-- Detail row --}}
                                    <div
                                        class="flex flex-wrap items-center gap-x-3 gap-y-1
                            text-xs text-gray-500 mb-3">
                                        <span class="flex items-center gap-1.5">
                                            <i class="ti ti-building text-gray-700 text-xs"></i>
                                            {{ $order->showtime->studio->cinema->name }}
                                        </span>
                                        <span class="text-gray-700">·</span>
                                        <span class="flex items-center gap-1.5">
                                            <i class="ti ti-calendar text-gray-700 text-xs"></i>
                                            {{ $order->showtime->start_time->translatedFormat('D, d M Y') }}
                                        </span>
                                        <span class="text-gray-700">·</span>
                                        <span class="flex items-center gap-1.5 text-red-400 font-medium">
                                            <i class="ti ti-clock text-red-500 text-xs"></i>
                                            {{ $order->showtime->start_time->format('H:i') }} WIB
                                        </span>
                                    </div>

                                    {{-- Seats + Total --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            {{-- Seat chips --}}
                                            @foreach ($order->orderItems->take(4) as $item)
                                                <span
                                                    class="text-[10px] font-bold bg-red-600/10
                                    border border-red-500/20 text-red-400
                                    px-2 py-0.5 rounded-lg">
                                                    {{ $item->seat_number }}
                                                </span>
                                            @endforeach
                                            @if ($order->orderItems->count() > 4)
                                                <span class="text-[10px] text-gray-600">
                                                    +{{ $order->orderItems->count() - 4 }} lainnya
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm font-black text-white flex-shrink-0">
                                            Rp {{ number_format($order->total, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                <i
                                    class="ti ti-chevron-right text-gray-700 text-base flex-shrink-0
                        group-hover:text-white group-hover:translate-x-0.5
                        transition-all duration-200 mt-1 hidden sm:block"></i>

                            </div>

                            {{-- Pending countdown --}}
                            @if ($order->status === 'pending' && $order->expired_at && now()->lt($order->expired_at))
                                <div class="mx-5 mb-3 flex items-center gap-2 bg-amber-500/10
                    border border-amber-500/20 rounded-xl px-3 py-2"
                                    x-data="miniCountdown('{{ $order->expired_at->toISOString() }}')" x-init="start()">
                                    <i class="ti ti-clock text-amber-400 text-sm flex-shrink-0"></i>
                                    <p class="text-xs text-amber-400 font-medium flex-1">
                                        Selesaikan pembayaran sebelum
                                    </p>
                                    <span class="text-xs font-black text-amber-300 font-mono" x-text="display"
                                        x-show="!expired"></span>
                                    <span x-show="expired" class="text-xs text-red-400 font-semibold">
                                        Waktu habis!
                                    </span>
                                </div>
                            @endif
                        </a>

                        {{-- Action bar --}}
                        <div class="px-5 pb-4 flex items-center gap-2">

                            @if ($order->status === 'pending')
                                {{-- Pay button --}}
                                <a href="{{ route('cinema.orders.show', $order->id) }}"
                                    class="inline-flex items-center gap-1.5 h-8 px-3 bg-red-600
                        hover:bg-red-500 text-white text-xs font-bold rounded-lg
                        transition-all duration-200">
                                    <i class="ti ti-credit-card text-sm"></i>
                                    Bayar Sekarang
                                </a>

                                {{-- Cancel trigger --}}
                                <button @click="cancelOpen = !cancelOpen"
                                    class="inline-flex items-center gap-1.5 h-8 px-3 border
                        border-white/10 text-gray-500 text-xs rounded-lg
                        hover:border-red-500/30 hover:text-red-400 hover:bg-red-500/5
                        transition-all duration-200">
                                    <i class="ti ti-x text-sm"></i>
                                    Batalkan
                                </button>
                            @elseif($order->status === 'paid')
                                {{-- View tickets --}}
                                <a href="{{ route('cinema.my-tickets', ['order' => $order->id]) }}"
                                    class="inline-flex items-center gap-1.5 h-8 px-3 bg-emerald-500/10
                        border border-emerald-500/20 text-emerald-400 text-xs font-semibold
                        rounded-lg hover:bg-emerald-500/20 transition-all duration-200">
                                    <i class="ti ti-ticket text-sm"></i>
                                    Lihat Tiket
                                </a>
                            @endif

                            {{-- Detail link --}}
                            <a href="{{ route('cinema.orders.show', $order->id) }}"
                                class="inline-flex items-center gap-1.5 h-8 px-3 border
                        border-white/10 text-gray-500 text-xs rounded-lg
                        hover:text-white hover:border-white/20
                        transition-all duration-200 ml-auto">
                                Detail
                                <i class="ti ti-arrow-right text-xs"></i>
                            </a>

                        </div>

                        {{-- Inline cancel form --}}
                        <div x-show="cancelOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="border-t border-white/5 bg-red-500/5 px-5 py-4">

                            <p class="text-xs font-semibold text-red-400 mb-3 flex items-center gap-2">
                                <i class="ti ti-alert-triangle text-sm"></i>
                                Konfirmasi Pembatalan Order
                            </p>

                            <form action="{{ route('cinema.orders.cancel', $order->id) }}" method="POST"
                                class="space-y-3">
                                @csrf @method('PATCH')

                                {{-- Quick reasons --}}
                                <div class="flex flex-wrap gap-2" x-data="{ sel: '' }">
                                    @foreach (['Salah jadwal', 'Tidak bisa hadir', 'Ingin ganti film', 'Lainnya'] as $reason)
                                        <button type="button"
                                            @click="sel = '{{ $reason }}';
                                $el.closest('form').querySelector('textarea').value = '{{ $reason }}';"
                                            :class="sel === '{{ $reason }}'
                                                ?
                                                'border-red-500/40 bg-red-500/10 text-red-400' :
                                                'border-white/10 text-gray-500 hover:border-white/20'"
                                            class="text-xs border rounded-lg px-2.5 py-1.5
                                transition-all duration-150">
                                            {{ $reason }}
                                        </button>
                                    @endforeach
                                </div>

                                <textarea name="cancel_reason" rows="2" placeholder="Tuliskan alasan pembatalan... (min. 10 karakter)"
                                    class="w-full bg-white/5 border border-white/10 text-white
                            text-sm rounded-xl px-3 py-2.5 outline-none resize-none
                            placeholder-gray-600 focus:border-red-500/30
                            transition-all duration-200"></textarea>

                                <div class="flex gap-2">
                                    <button type="button" @click="cancelOpen = false"
                                        class="flex-1 h-9 border border-white/10 text-gray-500
                                text-xs font-medium rounded-xl hover:text-white
                                hover:border-white/20 transition-all duration-200">
                                        Kembali
                                    </button>
                                    <button type="submit"
                                        onclick="return confirm('Yakin ingin membatalkan order ini?')"
                                        class="flex-1 h-9 bg-red-600 hover:bg-red-500 text-white
                                text-xs font-bold rounded-xl transition-all duration-200
                                flex items-center justify-center gap-1.5">
                                        <i class="ti ti-x text-sm"></i>
                                        Ya, Batalkan
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($orders->hasPages())
                <div class="mt-6 flex justify-center">
                    <div class="flex items-center gap-2">
                        @if ($orders->onFirstPage())
                            <span
                                class="w-9 h-9 flex items-center justify-center border
                border-white/5 rounded-xl text-gray-700 cursor-not-allowed">
                                <i class="ti ti-chevron-left text-sm"></i>
                            </span>
                        @else
                            <a href="{{ $orders->previousPageUrl() }}"
                                class="w-9 h-9 flex items-center justify-center border
                    border-white/10 rounded-xl text-gray-500 hover:text-white
                    hover:border-white/20 transition-all duration-200">
                                <i class="ti ti-chevron-left text-sm"></i>
                            </a>
                        @endif

                        @foreach ($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="w-9 h-9 flex items-center justify-center border rounded-xl
                    text-sm font-medium transition-all duration-200
                    {{ $page === $orders->currentPage()
                        ? 'bg-red-600 border-red-600 text-white'
                        : 'border-white/10 text-gray-500 hover:text-white hover:border-white/20' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($orders->hasMorePages())
                            <a href="{{ $orders->nextPageUrl() }}"
                                class="w-9 h-9 flex items-center justify-center border
                    border-white/10 rounded-xl text-gray-500 hover:text-white
                    hover:border-white/20 transition-all duration-200">
                                <i class="ti ti-chevron-right text-sm"></i>
                            </a>
                        @else
                            <span
                                class="w-9 h-9 flex items-center justify-center border
                border-white/5 rounded-xl text-gray-700 cursor-not-allowed">
                                <i class="ti ti-chevron-right text-sm"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

        @endif

    </div>

    @push('scripts')
        <script>
            function miniCountdown(expiresAt) {
                return {
                    display: '',
                    expired: false,

                    start() {
                        const end = new Date(expiresAt).getTime();
                        const tick = () => {
                            const diff = end - Date.now();
                            if (diff <= 0) {
                                this.expired = true;
                                setTimeout(() => window.location.reload(), 2000);
                                return;
                            }
                            const m = Math.floor(diff / 60000).toString().padStart(2, '0');
                            const s = Math.floor((diff % 60000) / 1000).toString().padStart(2, '0');
                            this.display = `${m}:${s}`;
                            setTimeout(tick, 1000);
                        };
                        tick();
                    }
                }
            }
        </script>
    @endpush

</x-cinema-layout>
