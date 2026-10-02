<x-cinema-layout>
    <x-slot name="title">Checkout — {{ $showtime->movie->title }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="cinemaCheckout()">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('cinema.seat-map', $showtime) }}"
                class="w-9 h-9 bg-gray-900 border border-white/10 rounded-xl flex items-center
                justify-center text-gray-400 hover:text-white hover:border-white/20
                transition-all duration-200 flex-shrink-0">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-white">Checkout</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Selesaikan pembayaran sebelum waktu habis
                </p>
            </div>

            {{-- Countdown --}}
            <div class="ml-auto flex items-center gap-2 bg-amber-500/10 border
            border-amber-500/20 text-amber-400 text-sm font-bold
            px-4 py-2 rounded-xl"
                x-data="countdownTimer('{{ $lockedUntil->toISOString() }}')" x-init="start()">
                <i class="ti ti-clock text-base"></i>
                <span x-text="display" x-show="!expired"></span>
                <span x-show="expired" class="text-red-400">Waktu Habis!</span>
            </div>
        </div>

        {{-- Progress steps --}}
        <div class="flex items-center gap-0 mb-8">
            @foreach ([['step' => 1, 'label' => 'Pilih Film', 'done' => true], ['step' => 2, 'label' => 'Pilih Kursi', 'done' => true], ['step' => 3, 'label' => 'Checkout', 'done' => false, 'active' => true], ['step' => 4, 'label' => 'Selesai', 'done' => false]] as $i => $step)
                <div class="flex items-center {{ $i > 0 ? 'flex-1' : '' }}">
                    @if ($i > 0)
                        <div class="flex-1 h-px {{ $step['done'] ? 'bg-red-600' : 'bg-white/10' }}"></div>
                    @endif
                    <div class="flex flex-col items-center gap-1 flex-shrink-0">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center
                    text-xs font-bold transition-all duration-300
                    {{ ($step['done']
                            ? 'bg-red-600 text-white'
                            : $step['active'] ?? false)
                        ? 'bg-red-600/20 border-2 border-red-500 text-red-400'
                        : 'bg-gray-800 border border-white/10 text-gray-600' }}">
                            @if ($step['done'])
                                <i class="ti ti-check text-sm"></i>
                            @else
                                {{ $step['step'] }}
                            @endif
                        </div>
                        <span
                            class="text-[10px] whitespace-nowrap
                    {{ $step['done'] || ($step['active'] ?? false) ? 'text-white font-medium' : 'text-gray-600' }}">
                            {{ $step['label'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Detail --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Showtime info --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h2 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                        <i class="ti ti-movie text-red-500"></i>
                        Detail Film
                    </h2>
                    <div class="flex items-start gap-4">
                        <div
                            class="w-16 h-24 bg-gray-800 rounded-xl overflow-hidden
                        flex-shrink-0 border border-white/5">
                            <img src="{{ $showtime->movie->poster_url }}" class="w-full h-full object-cover"
                                alt="{{ $showtime->movie->title }}">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-white mb-3 leading-snug">
                                {{ $showtime->movie->title }}
                            </h3>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ([['icon' => 'ti-building', 'label' => $showtime->studio->cinema->name], ['icon' => 'ti-movie', 'label' => $showtime->studio->name], ['icon' => 'ti-calendar', 'label' => $showtime->start_time->translatedFormat('l, d M Y')], ['icon' => 'ti-clock', 'label' => $showtime->start_time->format('H:i') . ' WIB', 'color' => 'text-red-400'], ['icon' => 'ti-3d-cube-sphere', 'label' => $showtime->format_label, 'color' => 'text-blue-400'], ['icon' => 'ti-language', 'label' => $showtime->language_label]] as $info)
                                    <div class="flex items-center gap-2 text-xs">
                                        <i
                                            class="ti {{ $info['icon'] }} text-gray-600 text-sm
                                    flex-shrink-0"></i>
                                        <span class="{{ $info['color'] ?? 'text-gray-400' }}">
                                            {{ $info['label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Seats detail --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h2 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                        <i class="ti ti-armchair text-red-500"></i>
                        Detail Kursi
                        <span class="text-gray-500 font-normal text-xs">
                            ({{ $lockedSeats->count() }} kursi)
                        </span>
                    </h2>

                    <div class="space-y-3 mb-5">
                        @foreach ($lockedSeats as $idx => $seat)
                            @php
                                $seatType = $seat->seatLayout->seat_type;
                                $defaultType = in_array($seatType, ['vip', 'couple']) ? 'vip' : 'regular';
                                $price = $prices[$defaultType];
                            @endphp
                            <div class="flex items-center gap-4 bg-white/5 rounded-xl p-4" x-data="{ ticketType: '{{ $defaultType }}' }">

                                {{-- Seat badge --}}
                                <div
                                    class="w-14 h-14 bg-red-600/10 border border-red-500/20
                            rounded-2xl flex flex-col items-center justify-center
                            flex-shrink-0">
                                    <span class="text-[10px] text-red-400 font-semibold">Kursi</span>
                                    <span class="text-lg font-black text-red-400">
                                        {{ $seat->seatLayout->seat_number }}
                                    </span>
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-sm font-bold text-white">
                                            Kursi {{ $seat->seatLayout->seat_number }}
                                        </span>
                                        <span
                                            class="text-[10px] font-medium px-2 py-0.5 rounded-lg
                                    {{ $seatType === 'vip' || $seatType === 'couple'
                                        ? 'bg-violet-900/30 border border-violet-500/30 text-violet-400'
                                        : 'bg-gray-800 border border-white/10 text-gray-500' }}">
                                            {{ ucfirst($seatType) }}
                                        </span>
                                    </div>

                                    {{-- Ticket type select --}}
                                    <div class="flex items-center gap-3">
                                        <label class="text-xs text-gray-500">Tipe:</label>
                                        <select name="seats[{{ $idx }}][ticket_type]" x-model="ticketType"
                                            @change="updateSeatPrice({{ $seat->seatLayout->id }}, ticketType)"
                                            class="bg-gray-800 border border-white/10 text-white text-xs
                                        rounded-xl px-3 h-8 outline-none cursor-pointer
                                        focus:border-white/20">
                                            @if (in_array($seatType, ['vip', 'couple']))
                                                <option value="vip">VIP — Rp
                                                    {{ number_format($prices['vip'], 0, ',', '.') }}</option>
                                            @else
                                                <option value="regular">Regular — Rp
                                                    {{ number_format($prices['regular'], 0, ',', '.') }}</option>
                                                @if ($prices['student'] > 0)
                                                    <option value="student">Pelajar — Rp
                                                        {{ number_format($prices['student'], 0, ',', '.') }}</option>
                                                @endif
                                                @if ($prices['senior'] > 0)
                                                    <option value="senior">Lansia — Rp
                                                        {{ number_format($prices['senior'], 0, ',', '.') }}</option>
                                                @endif
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                {{-- Price --}}
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm font-black text-white" :data-price="getPrice(ticketType)">
                                        Rp {{ number_format($price, 0, ',', '.') }}
                                    </p>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Pemesan info --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h2 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                        <i class="ti ti-user text-red-500"></i>
                        Data Pemesan
                    </h2>
                    <div class="flex items-center gap-4 p-4 bg-white/5 rounded-xl">
                        <div
                            class="w-12 h-12 rounded-full bg-red-600/20 border border-red-500/20
                        flex items-center justify-center text-red-400 text-sm font-bold
                        flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-white">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            @if (auth()->user()->phone)
                                <p class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->phone }}</p>
                            @endif
                        </div>
                        <a href="{{ route('user.profile.edit') }}"
                            class="text-xs text-gray-500 hover:text-white transition
                            flex items-center gap-1">
                            <i class="ti ti-edit text-sm"></i>
                            Edit
                        </a>
                    </div>

                    @if (!auth()->user()->phone)
                        <div
                            class="mt-3 p-3 bg-amber-500/10 border border-amber-500/20
                    rounded-xl flex items-start gap-2">
                            <i class="ti ti-alert-triangle text-amber-400 text-sm flex-shrink-0 mt-0.5"></i>
                            <p class="text-xs text-amber-300">
                                Tambahkan nomor HP di
                                <a href="{{ route('user.profile.edit') }}" class="underline font-semibold">profil</a>
                                untuk info tiket yang lebih lengkap.
                            </p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Right: Summary + Pay --}}
            <div class="space-y-4">

                {{-- Order summary --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase
                    tracking-wider mb-4">
                        Ringkasan Order
                    </h3>

                    {{-- Seat list --}}
                    <div class="space-y-2 mb-4">
                        @foreach ($lockedSeats as $seat)
                            @php
                                $seatType = $seat->seatLayout->seat_type;
                                $defType = in_array($seatType, ['vip', 'couple']) ? 'vip' : 'regular';
                            @endphp
                            <div class="flex justify-between items-center text-sm"
                                data-seat-item="{{ $seat->seatLayout->id }}">
                                <span class="text-gray-500">
                                    Kursi {{ $seat->seatLayout->seat_number }}
                                </span>
                                <span class="font-semibold text-white seat-price" data-base="{{ $prices[$defType] }}">
                                    Rp {{ number_format($prices[$defType], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total --}}
                    <div class="border-t border-white/5 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span id="subtotal-display">
                                Rp
                                {{ number_format(
                                    $lockedSeats->sum(fn($s) => $prices[in_array($s->seatLayout->seat_type, ['vip', 'couple']) ? 'vip' : 'regular']),
                                    0,
                                    ',',
                                    '.',
                                ) }}
                            </span>
                        </div>
                        <div
                            class="flex justify-between text-base font-black text-white
                        pt-2 border-t border-white/5">
                            <span>Total Bayar</span>
                            <span class="text-red-400" id="total-display">
                                Rp
                                {{ number_format(
                                    $lockedSeats->sum(fn($s) => $prices[in_array($s->seatLayout->seat_type, ['vip', 'couple']) ? 'vip' : 'regular']),
                                    0,
                                    ',',
                                    '.',
                                ) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pay button --}}
                <div x-data="paymentHandler()">

                    <button @click="pay()" :disabled="loading"
                        class="w-full h-14 bg-red-600 hover:bg-red-500 text-white font-black
                        text-base rounded-2xl transition-all duration-200
                        disabled:opacity-50 disabled:cursor-not-allowed
                        flex items-center justify-center gap-3 shadow-xl
                        shadow-red-600/20 hover:-translate-y-0.5">
                        <span x-show="!loading" class="flex items-center gap-3">
                            <i class="ti ti-credit-card text-xl"></i>
                            Bayar Sekarang
                        </span>
                        <span x-show="loading" class="flex items-center gap-3">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>

                    {{-- Error --}}
                    <div x-show="errorMsg" x-transition
                        class="mt-3 p-3 bg-red-500/10
                    border border-red-500/20 rounded-xl">
                        <p class="text-xs text-red-400 flex items-start gap-2">
                            <i class="ti ti-alert-circle text-sm flex-shrink-0"></i>
                            <span x-text="errorMsg"></span>
                        </p>
                    </div>

                </div>

                {{-- Payment methods --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-4">
                    <p class="text-[10px] text-gray-600 uppercase tracking-wider mb-3">
                        Metode Pembayaran
                    </p>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ([['icon' => 'ti-device-mobile', 'label' => 'GoPay', 'color' => 'text-emerald-400'], ['icon' => 'ti-wallet', 'label' => 'OVO', 'color' => 'text-violet-400'], ['icon' => 'ti-cash', 'label' => 'Dana', 'color' => 'text-blue-400'], ['icon' => 'ti-qrcode', 'label' => 'QRIS', 'color' => 'text-gray-400'], ['icon' => 'ti-building-bank', 'label' => 'BCA', 'color' => 'text-blue-400'], ['icon' => 'ti-building-bank', 'label' => 'Mandiri', 'color' => 'text-amber-400'], ['icon' => 'ti-credit-card', 'label' => 'Visa', 'color' => 'text-white'], ['icon' => 'ti-credit-card', 'label' => 'Mastercard', 'color' => 'text-red-400']] as $method)
                            <div
                                class="flex flex-col items-center gap-1 bg-white/5
                        border border-white/5 rounded-xl p-2">
                                <i class="ti {{ $method['icon'] }} {{ $method['color'] }} text-base"></i>
                                <span class="text-[9px] text-gray-600">{{ $method['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Security --}}
                <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-4 space-y-2">
                    @foreach ([['icon' => 'ti-shield-check', 'text' => 'Pembayaran aman via Midtrans', 'color' => 'text-emerald-400'], ['icon' => 'ti-lock', 'text' => 'Data dienkripsi SSL 256-bit', 'color' => 'text-blue-400'], ['icon' => 'ti-refresh', 'text' => 'Refund mudah jika ada masalah', 'color' => 'text-amber-400']] as $sec)
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <i class="ti {{ $sec['icon'] }} {{ $sec['color'] }} text-sm flex-shrink-0"></i>
                            {{ $sec['text'] }}
                        </div>
                    @endforeach
                </div>

                {{-- Terms --}}
                <p class="text-[10px] text-gray-700 text-center leading-relaxed">
                    Dengan membayar, Anda menyetujui
                    <a href="{{ route('pages.terms') }}" class="text-gray-500 hover:text-white underline">
                        Syarat & Ketentuan
                    </a>
                    dan
                    <a href="{{ route('pages.privacy') }}" class="text-gray-500 hover:text-white underline">
                        Kebijakan Privasi
                    </a>
                    kami.
                </p>

            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Midtrans Snap --}}
        <script
            src="{{ config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>

        <script>
            const PRICES_MAP = @json($prices);

            // Countdown timer
            function countdownTimer(expiresAt) {
                return {
                    display: '',
                    expired: false,

                    start() {
                        const end = new Date(expiresAt).getTime();
                        const tick = () => {
                            const diff = end - Date.now();
                            if (diff <= 0) {
                                this.expired = true;
                                this.display = '00:00';
                                setTimeout(() => {
                                    alert('Waktu habis! Silakan pilih kursi kembali.');
                                    window.location.href = '{{ route('cinema.seat-map', $showtime) }}';
                                }, 1500);
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

            // Seat price updater
            function cinemaCheckout() {
                return {
                    updateSeatPrice(seatId, ticketType) {
                        const price = PRICES_MAP[ticketType] || PRICES_MAP.regular;
                        const item = document.querySelector(`[data-seat-item="${seatId}"]`);
                        if (item) {
                            const priceEl = item.querySelector('.seat-price');
                            if (priceEl) {
                                priceEl.dataset.base = price;
                                priceEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
                            }
                        }
                        this.recalcTotal();
                    },

                    getPrice(type) {
                        return PRICES_MAP[type] || PRICES_MAP.regular;
                    },

                    recalcTotal() {
                        const prices = [...document.querySelectorAll('.seat-price')];
                        const total = prices.reduce((sum, el) => sum + parseInt(el.dataset.base || 0), 0);
                        const fmt = new Intl.NumberFormat('id-ID').format(total);
                        const display = `Rp ${fmt}`;
                        document.getElementById('subtotal-display').textContent = display;
                        document.getElementById('total-display').textContent = display;
                    }
                }
            }

            // Payment handler
            function paymentHandler() {
                return {
                    loading: false,
                    errorMsg: '',

                    async pay() {
                        this.loading = true;
                        this.errorMsg = '';

                        // Build seats data dari localStorage atau DOM
                        const storedSeats = localStorage.getItem(
                            'cinema_seats_{{ $showtime->id }}'
                        );
                        let seats = storedSeats ? JSON.parse(storedSeats) : [];

                        // Fallback: ambil dari DOM jika localStorage kosong
                        if (!seats.length) {
                            @foreach ($lockedSeats as $idx => $seat)
                                seats.push({
                                    seat_layout_id: {{ $seat->seatLayout->id }},
                                    ticket_type: document.querySelectorAll(
                                        'select[name^="seats[{{ $idx }}]"]'
                                    )[0]?.value || 'regular',
                                });
                            @endforeach
                        }

                        try {
                            const res = await fetch(
                                '{{ route('cinema.checkout.store', $showtime) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        seats
                                    }),
                                }
                            );

                            const data = await res.json();

                            if (!data.success) {
                                this.errorMsg = data.message || 'Terjadi kesalahan.';
                                this.loading = false;
                                return;
                            }

                            // Hapus localStorage
                            localStorage.removeItem('cinema_seats_{{ $showtime->id }}');

                            // Buka Midtrans Snap
                            window.snap.pay(data.snap_token, {
                                onSuccess: (result) => {
                                    window.location.href =
                                        '{{ route('cinema.checkout.finish') }}?order_id=' +
                                        data.order_number;
                                },
                                onPending: (result) => {
                                    window.location.href =
                                        '{{ route('cinema.checkout.finish') }}?order_id=' +
                                        data.order_number;
                                },
                                onError: (result) => {
                                    this.errorMsg = 'Pembayaran gagal. Silakan coba lagi.';
                                    this.loading = false;
                                },
                                onClose: () => {
                                    this.loading = false;
                                },
                            });

                        } catch (err) {
                            this.errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                            this.loading = false;
                        }
                    }
                }
            }
        </script>

        @push('styles')
            <style>
                .h-13 {
                    height: 3.25rem;
                }

                .h-14 {
                    height: 3.5rem;
                }
            </style>
        @endpush
    @endpush

</x-cinema-layout>
