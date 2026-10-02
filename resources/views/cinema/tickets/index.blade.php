<x-cinema-layout>
    <x-slot name="title">Tiket Saya — {{ setting('app_name') }} Cinema</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-black text-white">Tiket Saya</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Semua e-ticket bioskop Anda dalam satu tempat
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
                <i class="ti ti-circle-check flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3 mb-6">
            @foreach ([
        [
            'tab' => 'upcoming',
            'label' => 'Akan Tayang',
            'count' => $stats['upcoming'],
            'icon' => 'ti-ticket',
            'color' => 'text-emerald-400',
            'bg' => 'bg-emerald-500/10',
            'ring' => 'border-emerald-500/20',
        ],
        [
            'tab' => 'used',
            'label' => 'Sudah Digunakan',
            'count' => $stats['used'],
            'icon' => 'ti-check',
            'color' => 'text-blue-400',
            'bg' => 'bg-blue-500/10',
            'ring' => 'border-blue-500/20',
        ],
        [
            'tab' => 'cancelled',
            'label' => 'Dibatalkan',
            'count' => $stats['cancelled'],
            'icon' => 'ti-x',
            'color' => 'text-gray-500',
            'bg' => 'bg-gray-700/30',
            'ring' => 'border-white/5',
        ],
    ] as $stat)
                <a href="{{ route('cinema.my-tickets', ['tab' => $stat['tab']]) }}"
                    class="group bg-gray-900 border rounded-2xl p-4 transition-all duration-200
                hover:-translate-y-0.5
                {{ $tab === $stat['tab'] ? $stat['ring'] . ' shadow-lg' : 'border-white/5 hover:border-white/10' }}">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-600">{{ $stat['label'] }}</p>
                        <div
                            class="w-8 h-8 {{ $stat['bg'] }} rounded-xl flex items-center
                    justify-center">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-sm"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black {{ $stat['color'] }}">
                        {{ $stat['count'] }}
                    </p>
                </a>
            @endforeach
        </div>

        {{-- Tab + Search --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
            {{-- Tabs --}}
            <div class="flex items-center bg-gray-900 border border-white/5
            rounded-2xl p-1 flex-shrink-0">
                @foreach ([['tab' => 'upcoming', 'label' => 'Akan Tayang', 'icon' => 'ti-clock'], ['tab' => 'used', 'label' => 'Selesai', 'icon' => 'ti-check'], ['tab' => 'cancelled', 'label' => 'Batal', 'icon' => 'ti-x']] as $t)
                    <a href="{{ route('cinema.my-tickets', array_merge(request()->except('tab', 'page'), ['tab' => $t['tab']])) }}"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs
                    font-semibold transition-all duration-200
                    {{ $tab === $t['tab'] ? 'bg-red-600 text-white shadow' : 'text-gray-500 hover:text-white' }}">
                        <i class="ti {{ $t['icon'] }} text-sm"></i>
                        {{ $t['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- Search --}}
            <div
                class="flex items-center gap-2 bg-gray-900 border border-white/10
            rounded-xl px-3 h-10 flex-1 focus-within:border-white/20
            transition-all duration-200">
                <i class="ti ti-search text-gray-600 text-sm flex-shrink-0"></i>
                <form method="GET" class="flex-1 flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari kode tiket atau judul film..."
                        class="bg-transparent border-none outline-none text-sm
                        text-white placeholder-gray-600 w-full">
                    @if ($search)
                        <a href="{{ route('cinema.my-tickets', ['tab' => $tab]) }}"
                            class="text-gray-600 hover:text-white transition flex-shrink-0">
                            <i class="ti ti-x text-xs"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Tickets grid --}}
        @if ($tickets->isEmpty())
            <div class="text-center py-20 bg-gray-900/50 border border-white/5 rounded-2xl">
                <i class="ti ti-ticket-off text-5xl text-gray-700 block mb-4"></i>
                <p class="text-base font-semibold text-gray-400 mb-2">
                    @if ($tab === 'upcoming')
                        Tidak ada tiket aktif
                    @elseif($tab === 'used')
                        Belum ada tiket yang digunakan
                    @else
                        Tidak ada tiket yang dibatalkan
                    @endif
                </p>
                <p class="text-sm text-gray-600 mb-6">
                    @if ($search)
                        Tidak ada tiket yang cocok dengan pencarian
                    @elseif($tab === 'upcoming')
                        Beli tiket bioskop sekarang dan nikmati film favoritmu!
                    @else
                        Tiket yang sudah {{ $tab === 'used' ? 'digunakan' : 'dibatalkan' }}
                        akan muncul di sini
                    @endif
                </p>
                @if ($tab === 'upcoming')
                    <a href="{{ route('cinema.movies') }}"
                        class="inline-flex items-center gap-2 h-11 px-6 bg-red-600
                hover:bg-red-500 text-white text-sm font-semibold rounded-xl
                transition-all duration-200">
                        <i class="ti ti-movie text-base"></i>
                        Lihat Film Sekarang
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($tickets as $ticket)
                    <a href="{{ route('cinema.tickets.show', $ticket->id) }}"
                        class="group relative bg-gray-900 border border-white/5 rounded-2xl
                overflow-hidden hover:border-red-500/30 hover:-translate-y-1
                hover:shadow-2xl hover:shadow-red-500/10
                transition-all duration-300 flex flex-col">

                        {{-- Ticket top --}}
                        <div class="flex items-start gap-3 p-4 border-b border-white/5">

                            {{-- Poster --}}
                            <div
                                class="w-14 h-20 bg-gray-800 rounded-xl overflow-hidden
                    flex-shrink-0 border border-white/5">
                                <img src="{{ $ticket->orderItem->cinemaOrder->showtime->movie->poster_url }}"
                                    alt="{{ $ticket->movie_title }}" loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105
                            transition-transform duration-500">
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-sm font-bold text-white line-clamp-2 leading-snug mb-1
                        group-hover:text-red-400 transition-colors duration-200">
                                    {{ $ticket->movie_title }}
                                </h3>
                                <p class="text-[10px] text-gray-600 mb-2">
                                    {{ $ticket->cinema_name }}
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        class="text-[10px] font-bold bg-blue-900/30
                            border border-blue-500/30 text-blue-400
                            px-2 py-0.5 rounded-full">
                                        {{ strtoupper($ticket->format) }}
                                    </span>
                                    <span
                                        class="text-[10px] bg-white/5 border border-white/10
                            text-gray-500 px-2 py-0.5 rounded-full">
                                        {{ ucfirst($ticket->language) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Status --}}
                            @php
                                $sCfg = [
                                    'active' => [
                                        'bg' => 'bg-emerald-500/10 border-emerald-500/20',
                                        'text' => 'text-emerald-400',
                                        'dot' => 'bg-emerald-400',
                                        'label' => 'Aktif',
                                    ],
                                    'used' => [
                                        'bg' => 'bg-blue-500/10 border-blue-500/20',
                                        'text' => 'text-blue-400',
                                        'dot' => 'bg-blue-400',
                                        'label' => 'Digunakan',
                                    ],
                                    'cancelled' => [
                                        'bg' => 'bg-red-500/10 border-red-500/20',
                                        'text' => 'text-red-400',
                                        'dot' => 'bg-red-400',
                                        'label' => 'Dibatalkan',
                                    ],
                                    'expired' => [
                                        'bg' => 'bg-gray-700/30 border-white/5',
                                        'text' => 'text-gray-500',
                                        'dot' => 'bg-gray-600',
                                        'label' => 'Expired',
                                    ],
                                ];
                                $sc = $sCfg[$ticket->status] ?? $sCfg['active'];
                            @endphp
                            <div
                                class="flex-shrink-0 flex items-center gap-1.5 {{ $sc['bg'] }}
                    border text-xs font-semibold px-2.5 py-1 rounded-full
                    {{ $sc['text'] }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}
                        {{ $ticket->status === 'active' ? 'animate-pulse' : '' }}">
                                </span>
                                {{ $sc['label'] }}
                            </div>

                        </div>

                        {{-- Dotted separator --}}
                        <div class="relative flex items-center px-4 py-0">
                            <div
                                class="w-4 h-4 bg-gray-950 rounded-full absolute -left-2
                    border-r border-white/5">
                            </div>
                            <div class="flex-1 border-t border-dashed border-white/10"></div>
                            <div
                                class="w-4 h-4 bg-gray-950 rounded-full absolute -right-2
                    border-l border-white/5">
                            </div>
                        </div>

                        {{-- Ticket bottom --}}
                        <div class="p-4 flex-1 flex flex-col gap-3">

                            {{-- Seat + Time --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] text-gray-600 uppercase tracking-wider mb-1">
                                        Kursi
                                    </p>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-2xl font-black text-white">
                                            {{ $ticket->seat_number }}
                                        </span>
                                        <span
                                            class="text-[10px] font-semibold px-2 py-0.5 rounded-lg
                                {{ $ticket->seat_type === 'vip' || $ticket->seat_type === 'couple'
                                    ? 'bg-violet-900/30 border border-violet-500/30 text-violet-400'
                                    : 'bg-gray-800 border border-white/10 text-gray-500' }}">
                                            {{ ucfirst($ticket->seat_type) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-600 uppercase tracking-wider mb-1">
                                        Tayang
                                    </p>
                                    <p class="text-sm font-bold text-white">
                                        {{ $ticket->show_time->format('H:i') }}
                                        <span class="text-gray-600 text-xs font-normal">WIB</span>
                                    </p>
                                    <p class="text-[10px] text-gray-600">
                                        {{ $ticket->show_time->translatedFormat('D, d M') }}
                                    </p>
                                </div>
                            </div>

                            {{-- QR preview --}}
                            <div
                                class="flex items-center justify-between
                    bg-white/[0.03] rounded-xl p-3 border border-white/5">
                                {{-- Mini QR preview --}}
                                <div
                                    class="w-10 h-10 bg-white rounded-lg flex items-center
                        justify-center flex-shrink-0 overflow-hidden">
                                    @if ($ticket->qr_code)
                                        <img src="{{ route('cinema.tickets.qr', $ticket->id) }}"
                                            class="w-full h-full object-contain" alt="QR Code" loading="lazy">
                                    @else
                                        <i class="ti ti-qrcode text-gray-900 text-xl"></i>
                                    @endif
                                </div>

                                {{-- Code --}}
                                <div class="flex-1 px-3 min-w-0">
                                    <p class="text-[10px] text-gray-600 mb-0.5">Kode Tiket</p>
                                    <p class="text-xs font-mono font-bold text-white tracking-wider truncate">
                                        {{ $ticket->ticket_code }}
                                    </p>
                                    <p class="text-[10px] text-gray-600 mt-0.5">
                                        Rp {{ number_format($ticket->price, 0, ',', '.') }}
                                        · {{ $ticket->ticket_type_label }}
                                    </p>
                                </div>

                                {{-- Arrow --}}
                                <i
                                    class="ti ti-chevron-right text-gray-700 text-base flex-shrink-0
                        group-hover:text-white group-hover:translate-x-0.5
                        transition-all duration-200"></i>
                            </div>

                        </div>

                        {{-- Studio name footer --}}
                        <div class="px-4 pb-3">
                            <p class="text-[10px] text-gray-700 truncate flex items-center gap-1">
                                <i class="ti ti-door text-gray-700 text-xs"></i>
                                {{ $ticket->studio_name }}
                            </p>
                        </div>

                        {{-- Used watermark --}}
                        @if ($ticket->status === 'used')
                            <div
                                class="absolute inset-0 flex items-center justify-center
                pointer-events-none">
                                <div
                                    class="border-2 border-blue-400/20 text-blue-400/20 text-2xl
                    font-black px-6 py-2 rounded-xl rotate-[-15deg] tracking-widest">
                                    USED
                                </div>
                            </div>
                        @endif

                        {{-- Cancelled watermark --}}
                        @if (in_array($ticket->status, ['cancelled', 'expired']))
                            <div
                                class="absolute inset-0 flex items-center justify-center
                pointer-events-none">
                                <div
                                    class="border-2 border-red-400/20 text-red-400/20 text-2xl
                    font-black px-6 py-2 rounded-xl rotate-[-15deg] tracking-widest">
                                    BATAL
                                </div>
                            </div>
                        @endif

                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($tickets->hasPages())
                <div class="mt-8 flex justify-center">
                    <div class="flex items-center gap-2">
                        @if ($tickets->onFirstPage())
                            <span
                                class="w-9 h-9 flex items-center justify-center border
                border-white/5 rounded-xl text-gray-700 cursor-not-allowed">
                                <i class="ti ti-chevron-left text-sm"></i>
                            </span>
                        @else
                            <a href="{{ $tickets->previousPageUrl() }}"
                                class="w-9 h-9 flex items-center justify-center border
                    border-white/10 rounded-xl text-gray-500 hover:text-white
                    hover:border-white/20 transition-all duration-200">
                                <i class="ti ti-chevron-left text-sm"></i>
                            </a>
                        @endif

                        @foreach ($tickets->getUrlRange(max(1, $tickets->currentPage() - 2), min($tickets->lastPage(), $tickets->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="w-9 h-9 flex items-center justify-center border rounded-xl
                    text-sm font-medium transition-all duration-200
                    {{ $page === $tickets->currentPage()
                        ? 'bg-red-600 border-red-600 text-white'
                        : 'border-white/10 text-gray-500 hover:text-white
                                               hover:border-white/20' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($tickets->hasMorePages())
                            <a href="{{ $tickets->nextPageUrl() }}"
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

</x-cinema-layout>
