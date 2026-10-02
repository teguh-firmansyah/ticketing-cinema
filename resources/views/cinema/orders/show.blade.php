<x-cinema-layout>
    <x-slot name="title">
        Detail Order {{ $order->order_number }}
        — {{ setting('app_name') }} Cinema
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Back --}}
        <a href="{{ route('cinema.my-orders') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500
            hover:text-white transition-colors duration-200 mb-6">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke Pesanan
        </a>

        @if (session('success'))
            <div
                class="mb-5 flex items-center gap-3 bg-emerald-500/10 border
        border-emerald-500/20 text-emerald-400 text-sm px-4 py-3 rounded-xl">
                <i class="ti ti-circle-check text-base flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Status Banner --}}
        @php
            $bannerCfg = [
                'pending' => [
                    'bg' => 'bg-amber-500/10 border-amber-500/20',
                    'icon' => 'ti-clock',
                    'ic' => 'text-amber-400',
                    'title' => 'Menunggu Pembayaran',
                    'desc' => 'Selesaikan pembayaran sebelum batas waktu untuk mendapatkan tiket.',
                ],
                'paid' => [
                    'bg' => 'bg-emerald-500/10 border-emerald-500/20',
                    'icon' => 'ti-circle-check',
                    'ic' => 'text-emerald-400',
                    'title' => 'Pembayaran Berhasil!',
                    'desc' => 'Tiket Anda sudah siap. Tunjukkan QR code di pintu masuk studio.',
                ],
                'cancelled' => [
                    'bg' => 'bg-gray-700/30 border-white/5',
                    'icon' => 'ti-circle-x',
                    'ic' => 'text-gray-500',
                    'title' => 'Order Dibatalkan',
                    'desc' => 'Order ini telah dibatalkan. Kursi telah dikembalikan ke sistem.',
                ],
                'expired' => [
                    'bg' => 'bg-gray-700/20 border-white/5',
                    'icon' => 'ti-clock-off',
                    'ic' => 'text-gray-600',
                    'title' => 'Order Kadaluarsa',
                    'desc' => 'Batas waktu pembayaran telah lewat. Kursi telah dikembalikan ke sistem.',
                ],
                'refunded' => [
                    'bg' => 'bg-blue-500/10 border-blue-500/20',
                    'icon' => 'ti-refresh',
                    'ic' => 'text-blue-400',
                    'title' => 'Order Direfund',
                    'desc' => 'Dana akan dikembalikan ke metode pembayaran Anda.',
                ],
            ];
            $bc = $bannerCfg[$order->status] ?? $bannerCfg['pending'];
        @endphp

        <div class="flex items-start gap-4 p-5 border rounded-2xl
        {{ $bc['bg'] }} mb-6">
            <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center
            justify-center flex-shrink-0">
                <i class="ti {{ $bc['icon'] }} {{ $bc['ic'] }} text-2xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-base font-bold text-white mb-0.5">{{ $bc['title'] }}</p>
                <p class="text-sm text-gray-500">{{ $bc['desc'] }}</p>

                {{-- Pending: countdown --}}
                @if ($order->status === 'pending' && $order->expired_at && now()->lt($order->expired_at))
                    <div class="mt-3 flex items-center gap-2" x-data="showCountdown('{{ $order->expired_at->toISOString() }}')" x-init="start()">
                        <div class="flex items-center gap-2 font-mono" x-show="!expired">
                            <template x-for="unit in units" :key="unit.label">
                                <div class="text-center">
                                    <div
                                        class="w-11 h-11 bg-amber-900/40 border border-amber-500/30
                                rounded-xl flex items-center justify-center">
                                        <span class="text-base font-black text-amber-300" x-text="unit.val"></span>
                                    </div>
                                    <p class="text-[9px] text-amber-600 mt-1 uppercase tracking-wider"
                                        x-text="unit.label"></p>
                                </div>
                            </template>
                        </div>
                        <p x-show="expired" class="text-red-400 text-sm font-semibold">
                            Waktu habis!
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Pay Button (pending) --}}
        @if ($order->status === 'pending' && !$order->isExpired && $snapToken)
            <div class="mb-6" x-data="snapPayment()">
                <button @click="pay()" :disabled="loading"
                    class="w-full h-14 bg-red-600 hover:bg-red-500 text-white font-black
                text-base rounded-2xl transition-all duration-200
                disabled:opacity-50 disabled:cursor-not-allowed
                flex items-center justify-center gap-3
                shadow-xl shadow-red-600/20 hover:-translate-y-0.5">
                    <span x-show="!loading" class="flex items-center gap-3">
                        <i class="ti ti-credit-card text-xl"></i>
                        Bayar Sekarang —
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </span>
                    <span x-show="loading" class="flex items-center gap-3">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Membuka pembayaran...
                    </span>
                </button>
                <div x-show="errorMsg" x-transition class="mt-3 p-3 bg-red-500/10 border border-red-500/20 rounded-xl">
                    <p class="text-xs text-red-400 flex items-center gap-2">
                        <i class="ti ti-alert-circle text-sm"></i>
                        <span x-text="errorMsg"></span>
                    </p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Detail --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Order Info --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="text-sm font-bold text-white">Detail Pesanan</p>
                            <p class="text-xs font-mono text-gray-600 mt-0.5">
                                {{ $order->order_number }}
                            </p>
                        </div>
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
                            class="text-sm font-semibold px-3 py-1.5 rounded-xl border
                        {{ $statusCfg[$order->status] ?? '' }}">
                            {{ $order->status_label }}
                        </span>
                    </div>

                    {{-- Movie + Showtime --}}
                    <div class="flex items-start gap-4 p-4 bg-white/3 rounded-xl mb-5">
                        <div
                            class="w-16 h-24 bg-gray-800 rounded-xl overflow-hidden
                        flex-shrink-0 border border-white/5">
                            <img src="{{ $order->showtime->movie->poster_url }}"
                                alt="{{ $order->showtime->movie->title }}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-white mb-2 leading-snug">
                                {{ $order->showtime->movie->title }}
                            </h3>
                            <div class="space-y-1.5 text-xs text-gray-500">
                                <div class="flex items-center gap-2">
                                    <i class="ti ti-building text-gray-700 flex-shrink-0"></i>
                                    {{ $order->showtime->studio->cinema->name }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="ti ti-door text-gray-700 flex-shrink-0"></i>
                                    {{ $order->showtime->studio->name }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="ti ti-calendar text-gray-700 flex-shrink-0"></i>
                                    {{ $order->showtime->start_time->translatedFormat('l, d F Y') }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="ti ti-clock text-red-600 flex-shrink-0"></i>
                                    <span class="text-red-400 font-bold">
                                        {{ $order->showtime->start_time->format('H:i') }} WIB
                                    </span>
                                    <span class="text-gray-700">·</span>
                                    <span class="text-blue-400 font-semibold">
                                        {{ $order->showtime->format_label }}
                                    </span>
                                    <span class="text-gray-700">·</span>
                                    <span>{{ $order->showtime->language_label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Seat items --}}
                    <div class="space-y-2 mb-5">
                        <p class="text-xs text-gray-600 uppercase tracking-wider mb-3">
                            Detail Kursi
                        </p>
                        @foreach ($order->orderItems as $item)
                            <div
                                class="flex items-center gap-3 p-3 bg-white/3
                        rounded-xl border border-white/5">
                                <div
                                    class="w-12 h-12 bg-red-600/10 border border-red-500/20
                            rounded-xl flex flex-col items-center justify-center
                            flex-shrink-0">
                                    <span class="text-[10px] text-red-400">Kursi</span>
                                    <span class="text-sm font-black text-red-400">
                                        {{ $item->seat_number }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-white">
                                        Kursi {{ $item->seat_number }}
                                    </p>
                                    <p class="text-xs text-gray-600 mt-0.5">
                                        {{ $item->seat_type_label }}
                                        · {{ $item->ticket_type_label }}
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm font-bold text-white">
                                        Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                    {{-- Ticket status --}}
                                    @if ($item->ticket)
                                        @php
                                            $tBadge = [
                                                'active' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                                'used' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                'expired' => 'bg-gray-700/30 text-gray-600 border-white/5',
                                            ];
                                        @endphp
                                        <span
                                            class="text-[10px] font-semibold px-2 py-0.5
                                rounded-lg border inline-block mt-1
                                {{ $tBadge[$item->ticket->status] ?? '' }}">
                                            {{ $item->ticket->status_label }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Price breakdown --}}
                    <div class="border-t border-white/5 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal ({{ $order->orderItems->count() }} tiket)</span>
                            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if ($order->discount > 0)
                            <div class="flex justify-between text-sm text-emerald-400">
                                <span>Diskon</span>
                                <span>- Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div
                            class="flex justify-between text-base font-black text-white
                        pt-2 border-t border-white/5">
                            <span>Total Pembayaran</span>
                            <span class="text-red-400">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                </div>

                {{-- Tickets --}}
                @if ($order->status === 'paid' && $order->tickets->count() > 0)
                    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-bold text-white flex items-center gap-2">
                                <i class="ti ti-ticket text-red-500"></i>
                                Tiket Saya
                            </p>
                            <a href="{{ route('cinema.my-tickets', ['order' => $order->id]) }}"
                                class="text-xs text-gray-500 hover:text-white transition">
                                Lihat semua →
                            </a>
                        </div>
                        <div class="space-y-2">
                            @foreach ($order->tickets as $ticket)
                                <a href="{{ route('cinema.my-tickets') }}"
                                    class="flex items-center gap-3 p-3 border border-white/5
                            rounded-xl hover:border-white/10 transition-colors
                            duration-200 group">
                                    <div
                                        class="w-9 h-9 bg-gray-800 rounded-xl flex items-center
                            justify-center flex-shrink-0">
                                        <i
                                            class="ti ti-qrcode text-gray-500 text-base
                                group-hover:text-white transition-colors duration-200"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-mono font-bold text-white truncate">
                                            {{ $ticket->ticket_code }}
                                        </p>
                                        <p class="text-[10px] text-gray-600 mt-0.5">
                                            Kursi {{ $ticket->seat_number }}
                                            · {{ $ticket->ticket_type_label }}
                                        </p>
                                    </div>
                                    @php
                                        $tBadge2 = [
                                            'active' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'used' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'expired' => 'bg-gray-700/30 text-gray-600 border-white/5',
                                        ];
                                    @endphp
                                    <span
                                        class="text-[10px] font-semibold px-2 py-0.5 rounded-lg
                            border flex-shrink-0
                            {{ $tBadge2[$ticket->status] ?? '' }}">
                                        {{ $ticket->status_label }}
                                    </span>
                                    <i
                                        class="ti ti-arrow-right text-gray-700 text-sm flex-shrink-0
                            group-hover:text-white group-hover:translate-x-0.5
                            transition-all duration-200"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Cancel section (pending) --}}
                @if ($order->status === 'pending' && !$order->isExpired)
                    <div class="bg-gray-900 border border-white/5 rounded-2xl overflow-hidden"
                        x-data="{ open: false }">
                        <div class="p-5">
                            <button @click="open = !open"
                                class="text-sm text-gray-600 hover:text-red-400
                            flex items-center gap-2 transition-colors duration-200">
                                <i class="ti ti-x text-base"></i>
                                Batalkan Order Ini
                            </button>
                        </div>

                        <div x-show="open" x-transition class="border-t border-white/5 bg-red-500/5 p-5">
                            <div
                                class="flex items-start gap-2 mb-4 p-3 bg-amber-500/10
                        border border-amber-500/20 rounded-xl">
                                <i
                                    class="ti ti-alert-triangle text-amber-400 text-sm
                            flex-shrink-0 mt-0.5"></i>
                                <p class="text-xs text-amber-300 leading-relaxed">
                                    Order yang dibatalkan tidak dapat dipulihkan.
                                    Kursi akan dikembalikan ke sistem.
                                </p>
                            </div>

                            <form action="{{ route('cinema.orders.cancel', $order->id) }}" method="POST"
                                class="space-y-3">
                                @csrf @method('PATCH')

                                <div class="flex flex-wrap gap-2" x-data="{ sel: '' }">
                                    @foreach (['Salah jadwal', 'Tidak bisa hadir', 'Ingin ganti film', 'Lainnya'] as $reason)
                                        <button type="button"
                                            @click="sel = '{{ $reason }}';
                                    document.getElementById('cancel-reason-show').value = '{{ $reason }}';"
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

                                <textarea id="cancel-reason-show" name="cancel_reason" rows="3"
                                    placeholder="Tuliskan alasan pembatalan... (min. 10 karakter)"
                                    class="w-full bg-white/5 border border-white/10 text-white
                                text-sm rounded-xl px-3 py-2.5 outline-none resize-none
                                placeholder-gray-600 focus:border-red-500/30
                                transition-all duration-200"></textarea>

                                @error('cancel_reason')
                                    <p class="text-xs text-red-400">{{ $message }}</p>
                                @enderror

                                <div class="flex gap-2">
                                    <button type="button" @click="open = false"
                                        class="flex-1 h-10 border border-white/10 text-gray-500
                                    text-sm rounded-xl hover:text-white hover:border-white/20
                                    transition-all duration-200">
                                        Kembali
                                    </button>
                                    <button type="submit"
                                        onclick="return confirm('Yakin ingin membatalkan order ini?')"
                                        class="flex-1 h-10 bg-red-600 hover:bg-red-500 text-white
                                    text-sm font-bold rounded-xl transition-all duration-200
                                    flex items-center justify-center gap-2">
                                        <i class="ti ti-x text-base"></i>
                                        Ya, Batalkan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-4">

                {{-- Order meta --}}
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                    <h3 class="text-xs font-semibold text-gray-600 uppercase
                    tracking-wider mb-4">
                        Informasi Order
                    </h3>
                    <div class="space-y-3 text-sm">
                        @foreach ([['label' => 'No. Order', 'value' => $order->order_number, 'mono' => true], ['label' => 'Dibuat', 'value' => $order->created_at->format('d M Y, H:i')], ['label' => 'Total Tiket', 'value' => $order->orderItems->count() . ' tiket'], ['label' => 'Total Bayar', 'value' => 'Rp ' . number_format($order->total, 0, ',', '.'), 'bold' => true]] as $info)
                            <div class="flex justify-between items-start gap-2">
                                <span class="text-gray-600 flex-shrink-0">{{ $info['label'] }}</span>
                                <span
                                    class="{{ $info['mono'] ?? false ? 'font-mono text-xs' : '' }}
                            {{ $info['bold'] ?? false ? 'font-bold text-white' : 'text-gray-400' }}
                            text-right">
                                    {{ $info['value'] }}
                                </span>
                            </div>
                        @endforeach

                        @if ($order->expired_at)
                            <div class="flex justify-between items-start gap-2">
                                <span class="text-gray-600">Batas Bayar</span>
                                <span
                                    class="text-xs text-right
                            {{ now()->gt($order->expired_at) ? 'text-red-500' : 'text-amber-400' }}">
                                    {{ $order->expired_at->format('d M Y, H:i') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Notes --}}
                @if ($order->notes)
                    <div class="bg-gray-900 border border-white/5 rounded-2xl p-5">
                        <h3
                            class="text-xs font-semibold text-gray-600 uppercase
                    tracking-wider mb-3">
                            Catatan
                        </h3>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            {{ $order->notes }}
                        </p>
                    </div>
                @endif

                {{-- Help --}}
                <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-5">
                    <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                        <i class="ti ti-help-circle text-sm text-blue-400"></i>
                        Butuh Bantuan?
                    </p>
                    <p class="text-xs text-gray-600 leading-relaxed mb-3">
                        Hubungi kami jika ada masalah dengan pesanan Anda.
                    </p>
                    @if (setting('social_whatsapp'))
                        <a href="https://wa.me/{{ setting('social_whatsapp') }}?text={{ urlencode('Halo, saya butuh bantuan untuk order bioskop ' . $order->order_number) }}"
                            target="_blank"
                            class="flex items-center gap-2 text-xs font-medium text-emerald-400
                        hover:text-emerald-300 transition-colors duration-200">
                            <i class="ti ti-brand-whatsapp text-base"></i>
                            Chat WhatsApp Support
                        </a>
                    @endif
                </div>

                {{-- Back to movies --}}
                <a href="{{ route('cinema.movies') }}"
                    class="flex items-center justify-center gap-2 h-11 bg-gray-900
                    border border-white/5 hover:border-white/10 text-gray-500
                    hover:text-white text-sm font-medium rounded-2xl
                    transition-all duration-200">
                    <i class="ti ti-movie text-base"></i>
                    Lihat Film Lainnya
                </a>

            </div>
        </div>
    </div>

    @if ($snapToken)
        <script
            src="{{ config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endif

    @push('scripts')
        <script>
            // Countdown timer (detail)
            function showCountdown(expiresAt) {
                return {
                    units: [{
                            label: 'JAM',
                            val: '00'
                        },
                        {
                            label: 'MENIT',
                            val: '00'
                        },
                        {
                            label: 'DETIK',
                            val: '00'
                        },
                    ],
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
                            this.units[0].val = Math.floor(diff / 3600000).toString().padStart(2, '0');
                            this.units[1].val = Math.floor((diff % 3600000) / 60000).toString().padStart(2, '0');
                            this.units[2].val = Math.floor((diff % 60000) / 1000).toString().padStart(2, '0');
                            setTimeout(tick, 1000);
                        };
                        tick();
                    }
                }
            }

            // Snap payment handler
            function snapPayment() {
                return {
                    loading: false,
                    errorMsg: '',

                    pay() {
                        this.loading = true;
                        this.errorMsg = '';
                        @if ($snapToken)
                            window.snap.pay('{{ $snapToken }}', {
                                onSuccess: () => {
                                    window.location.href =
                                        '{{ route('cinema.checkout.finish') }}?order_id={{ $order->order_number }}';
                                },
                                onPending: () => {
                                    window.location.reload();
                                },
                                onError: () => {
                                    this.errorMsg = 'Pembayaran gagal. Silakan coba lagi.';
                                    this.loading = false;
                                },
                                onClose: () => {
                                    this.loading = false;
                                },
                            });
                        @endif
                    }
                }
            }
        </script>
    @endpush

</x-cinema-layout>
