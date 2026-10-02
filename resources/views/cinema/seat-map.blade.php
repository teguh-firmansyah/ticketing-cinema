<x-cinema-layout>
    <x-slot name="title">
        Pilih Kursi — {{ $showtime->movie->title }}
        — {{ setting('app_name') }} Cinema
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="seatMap()" x-init="init()">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('cinema.movies.show', $showtime->movie->slug) }}"
                class="w-9 h-9 bg-gray-900 border border-white/10 rounded-xl flex items-center
                justify-center text-gray-400 hover:text-white hover:border-white/20
                transition-all duration-200 flex-shrink-0">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div class="flex-1 min-w-0">
                <h1 class="text-base font-bold text-white truncate">
                    {{ $showtime->movie->title }}
                </h1>
                <p class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                    <span>{{ $showtime->studio->cinema->name }}</span>
                    <span class="text-gray-700">·</span>
                    <span>{{ $showtime->studio->name }}</span>
                    <span class="text-gray-700">·</span>
                    <span class="text-red-400 font-medium">
                        {{ $showtime->start_time->translatedFormat('D, d M Y — H:i') }}
                    </span>
                    <span class="text-gray-700">·</span>
                    <span class="text-blue-400">{{ $showtime->format_label }}</span>
                    <span class="text-gray-700">·</span>
                    <span>{{ $showtime->language_label }}</span>
                </p>
            </div>

            {{-- Global lock timer --}}
            <div x-show="selectedSeats.length > 0" x-transition
                class="flex-shrink-0 flex items-center gap-2 bg-amber-500/10
                border border-amber-500/20 text-amber-400 text-xs font-semibold
                px-3 py-2 rounded-xl">
                <i class="ti ti-clock text-sm"></i>
                <span x-text="formatTimer(globalTimer)"></span>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Left: Seat Map --}}
            <div class="xl:col-span-2">

                {{-- Screen --}}
                <div class="text-center mb-8">
                    <div class="relative mx-auto max-w-sm">
                        <div
                            class="h-2 bg-gradient-to-r from-transparent via-white/40
                        to-transparent rounded-full shadow-lg shadow-white/20">
                        </div>
                        <div class="mt-1 text-[10px] text-gray-600 tracking-[4px] uppercase">
                            Layar
                        </div>
                        <div
                            class="absolute inset-x-0 -top-1 h-8
                        bg-gradient-to-b from-white/5 to-transparent blur-sm">
                        </div>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="flex items-center justify-center gap-5 mb-6">
                    @foreach ([['color' => 'bg-gray-700 border-gray-600', 'label' => 'Terisi'], ['color' => 'bg-emerald-500/20 border-emerald-500/40', 'label' => 'Tersedia'], ['color' => 'bg-violet-500/20 border-violet-500/40', 'label' => 'VIP'], ['color' => 'bg-red-500 border-red-400', 'label' => 'Dipilih'], ['color' => 'bg-amber-500/20 border-amber-500/40', 'label' => 'Dikunci']] as $legend)
                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                            <div class="w-4 h-4 rounded-sm border {{ $legend['color'] }}"></div>
                            {{ $legend['label'] }}
                        </div>
                    @endforeach
                </div>

                {{-- Seat Grid --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5 overflow-x-auto">
                    <div class="min-w-max mx-auto space-y-1.5">

                        @foreach ($seatMap as $rowLabel => $seats)
                            <div class="flex items-center gap-1.5">

                                {{-- Row label --}}
                                <span
                                    class="text-[10px] text-gray-700 w-5 text-right
                            flex-shrink-0 font-medium">
                                    {{ $rowLabel }}
                                </span>

                                {{-- Seats --}}
                                <div class="flex gap-1" id="row-{{ $rowLabel }}">
                                    @php $lastCol = 0; @endphp
                                    @foreach ($seats as $seat)
                                        {{-- Aisle gap --}}
                                        @if ($seat['col_number'] > $lastCol + 1 && $lastCol > 0)
                                            <div class="w-5"></div>
                                        @endif
                                        @php $lastCol = $seat['col_number']; @endphp

                                        {{-- Seat button --}}
                                        @if ($seat['seat_type'] === 'blocked')
                                            <div class="w-7 h-7"></div>
                                        @else
                                            <button type="button" data-seat-id="{{ $seat['seat_layout_id'] }}"
                                                data-seat-number="{{ $seat['seat_number'] }}"
                                                data-seat-type="{{ $seat['seat_type'] }}"
                                                data-status="{{ $seat['status'] }}"
                                                @click="toggleSeat({{ json_encode($seat) }})" :disabled="isBusy"
                                                class="seat-btn w-7 h-7 rounded-md text-[9px] font-bold
                                    border transition-all duration-150 relative flex items-center
                                    justify-center cursor-pointer select-none
                                    disabled:cursor-wait group
                                    @if ($seat['status'] === 'booked') bg-gray-700 border-gray-600 text-gray-600
                                        cursor-not-allowed
                                    @elseif($seat['status'] === 'locked')
                                        bg-amber-500/20 border-amber-500/30 text-amber-600
                                        cursor-not-allowed
                                    @elseif($seat['status'] === 'selected')
                                        bg-red-500 border-red-400 text-white
                                        shadow-sm shadow-red-500/50
                                    @elseif($seat['seat_type'] === 'vip' || $seat['seat_type'] === 'couple')
                                        bg-violet-500/20 border-violet-500/30 text-violet-400
                                        hover:bg-violet-500/40 hover:border-violet-400
                                        hover:scale-110
                                    @else
                                        bg-emerald-500/20 border-emerald-500/30 text-emerald-400
                                        hover:bg-emerald-500/40 hover:border-emerald-400
                                        hover:scale-110 @endif">

                                                {{-- Seat number (show on hover / selected) --}}
                                                <span
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity
                                    {{ $seat['status'] === 'selected' ? 'opacity-100' : '' }}
                                    text-[8px]">
                                                    {{ $seat['col_number'] }}
                                                </span>

                                                {{-- Checkmark jika selected --}}
                                                @if ($seat['status'] === 'selected')
                                                    <i class="ti ti-check text-[10px] absolute"></i>
                                                @endif

                                            </button>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Row label (right) --}}
                                <span
                                    class="text-[10px] text-gray-700 w-5 text-left
                            flex-shrink-0 font-medium">
                                    {{ $rowLabel }}
                                </span>

                            </div>
                        @endforeach

                    </div>
                </div>

                {{-- Seat stats --}}
                <div class="grid grid-cols-3 gap-3 mt-4">
                    @foreach ([['label' => 'Tersedia', 'value' => $seatStats['available'], 'color' => 'text-emerald-400'], ['label' => 'Terisi', 'value' => $seatStats['booked'], 'color' => 'text-gray-500'], ['label' => 'Total', 'value' => $seatStats['total'], 'color' => 'text-white']] as $stat)
                        <div class="bg-gray-900 border border-white/5 rounded-xl p-3 text-center">
                            <p class="text-lg font-black {{ $stat['color'] }}">{{ $stat['value'] }}</p>
                            <p class="text-[10px] text-gray-600">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>

            </div>

            {{-- Right: Order Summary --}}
            <div class="space-y-4">

                {{-- Showtime info card --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <div class="flex items-start gap-3 mb-4">
                        <div
                            class="w-14 h-20 bg-gray-800 rounded-xl overflow-hidden flex-shrink-0
                        border border-white/5">
                            <img src="{{ $showtime->movie->poster_url }}" class="w-full h-full object-cover"
                                alt="{{ $showtime->movie->title }}">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-white leading-snug mb-1 line-clamp-2">
                                {{ $showtime->movie->title }}
                            </h3>
                            <p class="text-xs text-gray-500 flex flex-col gap-1">
                                <span class="flex items-center gap-1.5">
                                    <i class="ti ti-building text-xs text-gray-600"></i>
                                    {{ $showtime->studio->cinema->name }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i class="ti ti-calendar text-xs text-gray-600"></i>
                                    {{ $showtime->start_time->translatedFormat('D, d M Y') }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i class="ti ti-clock text-xs text-red-500"></i>
                                    <span class="text-red-400 font-semibold">
                                        {{ $showtime->start_time->format('H:i') }} WIB
                                    </span>
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Format & Language --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <span
                            class="text-[10px] font-bold bg-blue-900/30 text-blue-400
                        border border-blue-500/30 px-2.5 py-1 rounded-full">
                            {{ $showtime->format_label }}
                        </span>
                        <span
                            class="text-[10px] font-medium bg-white/5 text-gray-500
                        border border-white/10 px-2.5 py-1 rounded-full">
                            {{ $showtime->language_label }}
                        </span>
                        <span
                            class="text-[10px] font-medium bg-white/5 text-gray-500
                        border border-white/10 px-2.5 py-1 rounded-full">
                            {{ $showtime->studio->name }}
                        </span>
                    </div>
                </div>

                {{-- Selected seats --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
                        Kursi Dipilih
                        <span class="text-white normal-case" x-text="'(' + selectedSeats.length + ' kursi)'">
                        </span>
                    </h3>

                    {{-- Empty state --}}
                    <div x-show="selectedSeats.length === 0" class="text-center py-6">
                        <i class="ti ti-armchair text-4xl text-gray-700 block mb-2"></i>
                        <p class="text-xs text-gray-600">
                            Klik kursi di denah untuk memilih
                        </p>
                    </div>

                    {{-- Seat list --}}
                    <div x-show="selectedSeats.length > 0" class="space-y-2 mb-4">
                        <template x-for="seat in selectedSeats" :key="seat.seat_layout_id">
                            <div class="flex items-center gap-3 bg-white/5 rounded-xl p-3">
                                {{-- Seat badge --}}
                                <div
                                    class="w-10 h-10 bg-red-600/20 border border-red-500/30
                                rounded-xl flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-black text-red-400" x-text="seat.seat_number"></span>
                                </div>

                                {{-- Info + ticket type select --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-400 capitalize"
                                        x-text="seat.seat_type === 'vip'
                                        ? 'VIP'
                                        : seat.seat_type === 'couple'
                                            ? 'Couple'
                                            : 'Regular'">
                                    </p>
                                    <select :id="'ticket-type-' + seat.seat_layout_id"
                                        @change="updateTicketType(seat.seat_layout_id, $event.target.value)"
                                        class="w-full bg-transparent text-xs text-white font-medium
                                        border-none outline-none mt-0.5 cursor-pointer">
                                        <template x-for="opt in getTicketOptions(seat.seat_type)"
                                            :key="opt.value">
                                            <option :value="opt.value" x-text="opt.label"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Price --}}
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs font-bold text-white"
                                        x-text="'Rp ' + formatNumber(getSeatPrice(seat))">
                                    </p>
                                    <button @click="removeSeat(seat)"
                                        class="text-red-500 hover:text-red-400 transition mt-1">
                                        <i class="ti ti-x text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Divider + Total --}}
                    <div x-show="selectedSeats.length > 0" class="border-t border-white/5 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span x-text="selectedSeats.length + ' tiket'"></span>
                            <span x-text="'Rp ' + formatNumber(subtotal)"></span>
                        </div>
                        <div
                            class="flex justify-between text-base font-black text-white
                        pt-2 border-t border-white/5">
                            <span>Total</span>
                            <span class="text-red-400" x-text="'Rp ' + formatNumber(subtotal)"></span>
                        </div>
                    </div>
                </div>

                {{-- Ticket type legend --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Harga Tiket
                    </h3>
                    <div class="space-y-2">
                        @foreach ([['key' => 'regular', 'label' => 'Regular'], ['key' => 'student', 'label' => 'Pelajar/Mahasiswa'], ['key' => 'senior', 'label' => 'Lansia (60+)'], ['key' => 'vip', 'label' => 'VIP / Couple']] as $pt)
                            @if ($prices[$pt['key']] > 0)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">{{ $pt['label'] }}</span>
                                    <span class="font-semibold text-white">
                                        Rp {{ number_format($prices[$pt['key']], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- CTA --}}
                <button @click="goToCheckout()" :disabled="selectedSeats.length === 0 || isBusy"
                    class="w-full h-13 bg-red-600 hover:bg-red-500 text-white font-bold
                    text-sm rounded-2xl transition-all duration-200
                    disabled:opacity-40 disabled:cursor-not-allowed
                    flex items-center justify-center gap-2 shadow-lg shadow-red-600/20
                    hover:-translate-y-0.5 hover:shadow-red-500/30">
                    <span x-show="!isBusy" class="flex items-center gap-2">
                        <i class="ti ti-ticket text-lg"></i>
                        Lanjut ke Pembayaran
                    </span>
                    <span x-show="isBusy" class="flex items-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>

                {{-- Max seat warning --}}
                <p class="text-center text-[11px] text-gray-700">
                    Maksimal 6 kursi per transaksi
                </p>

                {{-- Error message --}}
                <div x-show="errorMsg" x-transition class="bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                    <p class="text-xs text-red-400 flex items-start gap-2">
                        <i class="ti ti-alert-circle text-sm flex-shrink-0 mt-0.5"></i>
                        <span x-text="errorMsg"></span>
                    </p>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const PRICES = @json($prices);
            const MAX_SEATS = 6;
            const SHOWTIME_ID = {{ $showtime->id }};

            function seatMap() {
                return {
                    selectedSeats: [],
                    ticketTypes: {},
                    isBusy: false,
                    errorMsg: '',
                    globalTimer: 0,
                    timerInterval: null,
                    pollInterval: null,

                    // Init
                    init() {
                        // Init selected seats dari server (jika ada locked seats)
                        @foreach (collect($seatMap)->flatten(1)->where('status', 'selected') as $seat)
                            this.selectedSeats.push(@json($seat));
                            this.ticketTypes[{{ $seat['seat_layout_id'] }}] =
                                '{{ $seat['seat_type'] === 'vip' || $seat['seat_type'] === 'couple' ? 'vip' : 'regular' }}';
                        @endforeach

                        // Start timer jika ada selected seats
                        if (this.selectedSeats.length > 0) {
                            this.startTimer();
                        }

                        // Poll status setiap 5 detik
                        this.pollInterval = setInterval(() => this.pollStatus(), 5000);

                        // Extend lock tiap 8 menit
                        setInterval(() => {
                            if (this.selectedSeats.length > 0) {
                                this.extendLock();
                            }
                        }, 8 * 60 * 1000);
                    },

                    // Toggle seat
                    async toggleSeat(seat) {
                        if (['booked', 'locked'].includes(seat.status)) return;
                        if (this.isBusy) return;

                        if (seat.status === 'selected') {
                            await this.removeSeat(seat);
                        } else {
                            await this.addSeat(seat);
                        }
                    },

                    // Add seat
                    async addSeat(seat) {
                        if (this.selectedSeats.length >= MAX_SEATS) {
                            this.showError(`Maksimal ${MAX_SEATS} kursi per transaksi.`);
                            return;
                        }

                        this.isBusy = true;
                        this.errorMsg = '';

                        try {
                            const res = await fetch(
                                '{{ route('cinema.seat-map.lock', $showtime) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        seat_layout_id: seat.seat_layout_id,
                                    }),
                                }
                            );

                            const data = await res.json();

                            if (!data.success) {
                                this.showError(data.message || 'Kursi tidak tersedia.');
                                // Update seat status di UI
                                this.updateSeatStatus(seat.seat_layout_id, 'booked');
                                return;
                            }

                            // Update local state
                            seat.status = 'selected';
                            this.selectedSeats.push({
                                ...seat
                            });
                            this.ticketTypes[seat.seat_layout_id] =
                                seat.seat_type === 'vip' || seat.seat_type === 'couple' ?
                                'vip' :
                                'regular';

                            // Update seat button UI
                            this.updateSeatStatus(seat.seat_layout_id, 'selected');

                            // Start timer jika ini kursi pertama
                            if (this.selectedSeats.length === 1) {
                                this.globalTimer = data.remaining_secs;
                                this.startTimer();
                            }

                        } catch (err) {
                            this.showError('Terjadi kesalahan. Silakan coba lagi.');
                        } finally {
                            this.isBusy = false;
                        }
                    },

                    // Remove seat
                    async removeSeat(seat) {
                        this.isBusy = true;
                        this.errorMsg = '';

                        try {
                            await fetch(
                                '{{ route('cinema.seat-map.release', $showtime) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        seat_layout_id: seat.seat_layout_id,
                                    }),
                                }
                            );

                            // Update local state
                            this.selectedSeats = this.selectedSeats.filter(
                                s => s.seat_layout_id !== seat.seat_layout_id
                            );
                            delete this.ticketTypes[seat.seat_layout_id];

                            // Update seat button UI
                            const seatType = seat.seat_type;
                            this.updateSeatStatus(
                                seat.seat_layout_id,
                                'available',
                                seatType
                            );

                            // Stop timer jika tidak ada kursi
                            if (this.selectedSeats.length === 0) {
                                this.stopTimer();
                            }

                        } catch (err) {
                            this.showError('Gagal melepas kursi. Silakan coba lagi.');
                        } finally {
                            this.isBusy = false;
                        }
                    },

                    // Update ticket type
                    updateTicketType(seatLayoutId, type) {
                        this.ticketTypes[seatLayoutId] = type;
                    },

                    // Get ticket options berdasarkan seat type
                    getTicketOptions(seatType) {
                        if (seatType === 'vip' || seatType === 'couple') {
                            return [{
                                value: 'vip',
                                label: `VIP — Rp ${this.formatNumber(PRICES.vip)}`
                            }];
                        }
                        const opts = [{
                            value: 'regular',
                            label: `Regular — Rp ${this.formatNumber(PRICES.regular)}`
                        }, ];
                        if (PRICES.student > 0) {
                            opts.push({
                                value: 'student',
                                label: `Pelajar — Rp ${this.formatNumber(PRICES.student)}`
                            });
                        }
                        if (PRICES.senior > 0) {
                            opts.push({
                                value: 'senior',
                                label: `Lansia — Rp ${this.formatNumber(PRICES.senior)}`
                            });
                        }
                        return opts;
                    },

                    // Get price for seat
                    getSeatPrice(seat) {
                        const type = this.ticketTypes[seat.seat_layout_id] || 'regular';
                        return PRICES[type] || PRICES.regular;
                    },

                    // Computed: subtotal
                    get subtotal() {
                        return this.selectedSeats.reduce(
                            (sum, seat) => sum + this.getSeatPrice(seat),
                            0
                        );
                    },

                    // Poll status dari server
                    async pollStatus() {
                        try {
                            const res = await fetch(
                                '{{ route('cinema.seat-map.status', $showtime) }}', {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                }
                            );
                            const data = await res.json();

                            // Update status kursi yang berubah
                            data.seats.forEach(serverSeat => {
                                const btn = document.querySelector(
                                    `[data-seat-id="${serverSeat.seat_layout_id}"]`
                                );
                                if (!btn) return;

                                const currentStatus = btn.dataset.status;
                                const isMySelected = this.selectedSeats.some(
                                    s => s.seat_layout_id == serverSeat.seat_layout_id
                                );

                                // Jangan update kursi yang kita pilih sendiri
                                if (isMySelected) return;

                                if (currentStatus !== serverSeat.status) {
                                    this.updateSeatStatus(
                                        serverSeat.seat_layout_id,
                                        serverSeat.status
                                    );
                                }
                            });

                        } catch (err) {
                            // Silent fail untuk polling
                        }
                    },

                    // Update seat button UI
                    updateSeatStatus(seatLayoutId, status, seatType = null) {
                        const btn = document.querySelector(
                            `[data-seat-id="${seatLayoutId}"]`
                        );
                        if (!btn) return;

                        btn.dataset.status = status;

                        // Reset semua class
                        btn.className = btn.className
                            .replace(/bg-\S+|border-\S+|text-\S+|cursor-\S+|opacity-\S+|shadow-\S+/g, '')
                            .trim();

                        const baseClass = 'seat-btn w-7 h-7 rounded-md text-[9px] font-bold ' +
                            'border transition-all duration-150 relative flex items-center ' +
                            'justify-center select-none group';

                        const type = seatType || btn.dataset.seatType || 'regular';
                        let stateClass = '';

                        if (status === 'booked') {
                            stateClass = 'bg-gray-700 border-gray-600 text-gray-600 cursor-not-allowed';
                            btn.disabled = true;
                            btn.innerHTML = '';
                        } else if (status === 'locked') {
                            stateClass = 'bg-amber-500/20 border-amber-500/30 text-amber-600 cursor-not-allowed';
                            btn.disabled = true;
                            btn.innerHTML = '';
                        } else if (status === 'selected') {
                            stateClass = 'bg-red-500 border-red-400 text-white shadow-sm shadow-red-500/50 cursor-pointer';
                            btn.disabled = false;
                            btn.innerHTML = '<i class="ti ti-check text-[10px] absolute"></i>';
                        } else {
                            // Available
                            btn.disabled = false;
                            btn.innerHTML =
                                `<span class="opacity-0 group-hover:opacity-100 transition-opacity text-[8px]">${btn.dataset.seatNumber?.replace(/[A-Z]/g, '')}</span>`;
                            if (type === 'vip' || type === 'couple') {
                                stateClass = 'bg-violet-500/20 border-violet-500/30 text-violet-400 ' +
                                    'hover:bg-violet-500/40 hover:border-violet-400 hover:scale-110 cursor-pointer';
                            } else {
                                stateClass = 'bg-emerald-500/20 border-emerald-500/30 text-emerald-400 ' +
                                    'hover:bg-emerald-500/40 hover:border-emerald-400 hover:scale-110 cursor-pointer';
                            }
                        }

                        btn.className = `${baseClass} ${stateClass}`;
                    },

                    // Extend lock
                    async extendLock() {
                        try {
                            await fetch(
                                '{{ route('cinema.seat-map.extend', $showtime) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,
                                        'Accept': 'application/json',
                                    },
                                }
                            );
                            this.globalTimer = 10 * 60;
                        } catch (err) {}
                    },

                    // Go to checkout
                    goToCheckout() {
                        if (this.selectedSeats.length === 0) return;
                        const seats = this.selectedSeats.map(s => ({
                            seat_layout_id: s.seat_layout_id,
                            seat_number: s.seat_number,
                            seat_type: s.seat_type,
                            ticket_type: this.ticketTypes[s.seat_layout_id] || 'regular',
                            price: this.getSeatPrice(s),
                        }));
                        // Encode ke session/URL
                        localStorage.setItem('cinema_seats_{{ $showtime->id }}', JSON.stringify(seats));
                        window.location.href = '{{ route('cinema.checkout', $showtime) }}';
                    },

                    // Timer
                    startTimer() {
                        if (this.timerInterval) return;
                        this.timerInterval = setInterval(() => {
                            if (this.globalTimer > 0) {
                                this.globalTimer--;
                            } else {
                                this.stopTimer();
                                this.showError(
                                    'Waktu pemilihan kursi habis. Kursi telah dilepas.'
                                );
                                this.selectedSeats = [];
                                this.ticketTypes = {};
                                this.pollStatus();
                            }
                        }, 1000);
                    },

                    stopTimer() {
                        if (this.timerInterval) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                        }
                        this.globalTimer = 0;
                    },

                    formatTimer(secs) {
                        const m = Math.floor(secs / 60).toString().padStart(2, '0');
                        const s = (secs % 60).toString().padStart(2, '0');
                        return `${m}:${s}`;
                    },

                    // Helpers
                    formatNumber(n) {
                        return new Intl.NumberFormat('id-ID').format(n);
                    },

                    showError(msg) {
                        this.errorMsg = msg;
                        setTimeout(() => this.errorMsg = '', 5000);
                    },

                    // Destroy intervals on unmount
                    destroy() {
                        clearInterval(this.timerInterval);
                        clearInterval(this.pollInterval);
                    }
                }
            }
        </script>

        @push('styles')
            <style>
                .h-13 {
                    height: 3.25rem;
                }

                .seat-btn {
                    -webkit-tap-highlight-color: transparent;
                }
            </style>
        @endpush
    @endpush

</x-cinema-layout>
