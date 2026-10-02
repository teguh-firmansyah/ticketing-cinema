<footer class="bg-gray-950 border-t border-white/5">

    {{-- Newsletter Strip --}}
    <div class="border-b border-white/5 bg-red-600/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-5">
                <div>
                    <h3 class="text-base font-bold text-white mb-1">
                        Jangan Lewatkan Film Terbaru
                    </h3>
                    <p class="text-sm text-gray-500">
                        Daftar dan dapatkan notifikasi jadwal & promo tiket eksklusif.
                    </p>
                </div>
                <form class="flex gap-2 w-full sm:w-auto flex-shrink-0" @submit.prevent>
                    <div
                        class="flex items-center gap-2 bg-white/5 border border-white/10
                        rounded-xl px-3 h-11 flex-1 sm:w-64 focus-within:border-white/20
                        transition-all duration-200">
                        <i class="ti ti-mail text-gray-500 text-sm flex-shrink-0"></i>
                        <input type="email" placeholder="Email kamu..."
                            class="bg-transparent border-none outline-none text-sm
                                text-white placeholder-gray-500 w-full">
                    </div>
                    <button type="submit"
                        class="h-11 px-5 bg-red-600 hover:bg-red-500 text-white text-sm
                            font-semibold rounded-xl transition-all duration-200
                            flex items-center gap-2 flex-shrink-0">
                        <i class="ti ti-send text-sm"></i>
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Footer --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8">

            {{-- Col 1: Brand --}}
            <div class="lg:col-span-2">

                {{-- Logo --}}
                <a href="{{ route('cinema.index') }}" class="flex items-center gap-3 mb-5 w-fit group">
                    <div
                        class="w-10 h-10 bg-red-600 rounded-xl flex items-center
                        justify-center group-hover:bg-red-500 transition-colors duration-200">
                        @if (setting('app_logo'))
                            <img src="{{ Storage::url(setting('app_logo')) }}" class="w-6 h-6 object-contain"
                                alt="Logo">
                        @else
                            <i class="ti ti-movie text-white text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-base font-bold text-white tracking-tight leading-none">
                            {{ setting('app_name', config('app.name')) }}
                        </p>
                        <p
                            class="text-[10px] text-red-500 font-semibold tracking-widest
                            uppercase leading-none mt-0.5">
                            Cinema
                        </p>
                    </div>
                </a>

                <p class="text-sm text-gray-500 leading-relaxed mb-6 max-w-xs">
                    Platform bioskop online terpercaya Indonesia.
                    Pilih film, pilih kursi, bayar, dan nikmati!
                    Pengalaman menonton terbaik dimulai dari sini.
                </p>

                {{-- Kontak --}}
                <div class="space-y-3 mb-6">
                    @if (setting('app_email'))
                        <a href="mailto:{{ setting('app_email') }}"
                            class="flex items-center gap-3 text-sm text-gray-500
                            hover:text-white transition-colors duration-200 group">
                            <div
                                class="w-8 h-8 bg-white/5 rounded-xl flex items-center
                            justify-center group-hover:bg-white/10 transition-colors duration-200">
                                <i
                                    class="ti ti-mail text-gray-500 text-sm
                                group-hover:text-white transition-colors duration-200"></i>
                            </div>
                            {{ setting('app_email') }}
                        </a>
                    @endif
                    @if (setting('app_phone'))
                        <a href="tel:{{ setting('app_phone') }}"
                            class="flex items-center gap-3 text-sm text-gray-500
                            hover:text-white transition-colors duration-200 group">
                            <div
                                class="w-8 h-8 bg-white/5 rounded-xl flex items-center
                            justify-center group-hover:bg-white/10 transition-colors duration-200">
                                <i
                                    class="ti ti-phone text-gray-500 text-sm
                                group-hover:text-white transition-colors duration-200"></i>
                            </div>
                            {{ setting('app_phone') }}
                        </a>
                    @endif
                    @if (setting('social_whatsapp'))
                        <a href="https://wa.me/{{ setting('social_whatsapp') }}" target="_blank"
                            class="flex items-center gap-3 text-sm text-gray-500
                            hover:text-white transition-colors duration-200 group">
                            <div
                                class="w-8 h-8 bg-white/5 rounded-xl flex items-center
                            justify-center group-hover:bg-emerald-500/20
                            transition-colors duration-200">
                                <i
                                    class="ti ti-brand-whatsapp text-gray-500 text-sm
                                group-hover:text-emerald-400 transition-colors duration-200"></i>
                            </div>
                            WhatsApp Support
                        </a>
                    @endif
                </div>

                {{-- Social Media --}}
                <div>
                    <p
                        class="text-[10px] font-semibold text-gray-600 uppercase
                        tracking-widest mb-3">
                        Ikuti Kami
                    </p>
                    <div class="flex items-center gap-2">
                        @foreach ([['key' => 'social_instagram', 'icon' => 'ti-brand-instagram', 'href' => 'https://instagram.com/', 'color' => 'hover:bg-pink-500/20 hover:text-pink-400 hover:border-pink-500/30'], ['key' => 'social_twitter', 'icon' => 'ti-brand-x', 'href' => 'https://twitter.com/', 'color' => 'hover:bg-white/10 hover:text-white hover:border-white/20'], ['key' => 'social_facebook', 'icon' => 'ti-brand-facebook', 'href' => 'https://facebook.com/', 'color' => 'hover:bg-blue-500/20 hover:text-blue-400 hover:border-blue-500/30'], ['key' => 'social_youtube', 'icon' => 'ti-brand-youtube', 'href' => 'https://youtube.com/', 'color' => 'hover:bg-red-500/20 hover:text-red-400 hover:border-red-500/30'], ['key' => 'social_tiktok', 'icon' => 'ti-brand-tiktok', 'href' => 'https://tiktok.com/@', 'color' => 'hover:bg-white/10 hover:text-white hover:border-white/20']] as $social)
                            @if (setting($social['key']))
                                <a href="{{ $social['href'] . setting($social['key']) }}" target="_blank"
                                    rel="noopener noreferrer"
                                    class="w-9 h-9 border border-white/10 rounded-xl
                                flex items-center justify-center text-gray-500
                                {{ $social['color'] }} transition-all duration-200">
                                    <i class="ti {{ $social['icon'] }} text-sm"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Col 2: Film --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">
                    Film
                </h4>
                <ul class="space-y-3">
                    @foreach ([['route' => 'cinema.movies', 'label' => 'Sedang Tayang'], ['route' => 'cinema.coming', 'label' => 'Segera Hadir'], ['route' => 'cinema.movies', 'label' => 'Film Aksi'], ['route' => 'cinema.movies', 'label' => 'Film Animasi'], ['route' => 'cinema.movies', 'label' => 'Film Horor'], ['route' => 'cinema.movies', 'label' => 'Film Indonesia']] as $link)
                        <li>
                            <a href="{{ route($link['route']) }}"
                                class="text-sm text-gray-500 hover:text-white
                                transition-colors duration-200 flex items-center gap-2 group">
                                <i
                                    class="ti ti-chevron-right text-xs text-gray-700
                                group-hover:text-red-500 group-hover:translate-x-0.5
                                transition-all duration-200"></i>
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Col 3: Bioskop --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">
                    Bioskop
                </h4>
                <ul class="space-y-3">
                    @foreach ([['route' => 'cinema.cinemas', 'label' => 'Semua Bioskop'], ['route' => 'cinema.cinemas', 'label' => 'Jakarta'], ['route' => 'cinema.cinemas', 'label' => 'Surabaya'], ['route' => 'cinema.cinemas', 'label' => 'Bandung'], ['route' => 'cinema.cinemas', 'label' => 'Yogyakarta'], ['route' => 'cinema.cinemas', 'label' => 'Bali']] as $link)
                        <li>
                            <a href="{{ route($link['route']) }}"
                                class="text-sm text-gray-500 hover:text-white
                                transition-colors duration-200 flex items-center gap-2 group">
                                <i
                                    class="ti ti-chevron-right text-xs text-gray-700
                                group-hover:text-red-500 group-hover:translate-x-0.5
                                transition-all duration-200"></i>
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Col 4: Layanan --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">
                    Layanan
                </h4>
                <ul class="space-y-3">
                    @foreach ([['route' => 'cinema.my-tickets', 'label' => 'Tiket Saya', 'auth' => true], ['route' => 'cinema.my-orders', 'label' => 'Riwayat Order', 'auth' => true], ['route' => 'faqs.index', 'label' => 'Pusat Bantuan', 'auth' => false], ['route' => 'pages.privacy', 'label' => 'Kebijakan Privasi', 'auth' => false], ['route' => 'pages.terms', 'label' => 'Syarat & Ketentuan', 'auth' => false], ['route' => 'about.index', 'label' => 'Tentang Kami', 'auth' => false]] as $link)
                        @if (!$link['auth'] || auth()->check())
                            <li>
                                <a href="{{ route($link['route']) }}"
                                    class="text-sm text-gray-500 hover:text-white
                                transition-colors duration-200 flex items-center gap-2 group">
                                    <i
                                        class="ti ti-chevron-right text-xs text-gray-700
                                group-hover:text-red-500 group-hover:translate-x-0.5
                                transition-all duration-200"></i>
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>

                {{-- App badges --}}
                <div class="mt-6">
                    <p
                        class="text-[10px] font-semibold text-gray-600 uppercase
                        tracking-widest mb-3">
                        Download App
                    </p>
                    <div class="space-y-2">
                        <a href="#"
                            class="flex items-center gap-2.5 bg-white/5 border border-white/10
                                rounded-xl px-3 py-2.5 hover:bg-white/10 hover:border-white/20
                                transition-all duration-200 group">
                            <i class="ti ti-brand-apple text-white text-lg flex-shrink-0"></i>
                            <div>
                                <p class="text-[10px] text-gray-500 leading-none">
                                    Download di
                                </p>
                                <p class="text-xs font-semibold text-white leading-tight mt-0.5">
                                    App Store
                                </p>
                            </div>
                        </a>
                        <a href="#"
                            class="flex items-center gap-2.5 bg-white/5 border border-white/10
                                rounded-xl px-3 py-2.5 hover:bg-white/10 hover:border-white/20
                                transition-all duration-200 group">
                            <i class="ti ti-brand-google-play text-white text-lg flex-shrink-0"></i>
                            <div>
                                <p class="text-[10px] text-gray-500 leading-none">
                                    Tersedia di
                                </p>
                                <p class="text-xs font-semibold text-white leading-tight mt-0.5">
                                    Google Play
                                </p>
                            </div>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Bioskop Cities Strip --}}
    <div class="border-t border-white/5 bg-black/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="text-[10px] font-semibold text-gray-600 uppercase
                    tracking-widest mr-2">
                    Kota:
                </span>
                @foreach (['Jakarta', 'Surabaya', 'Bandung', 'Yogyakarta', 'Medan', 'Makassar', 'Semarang', 'Bali', 'Palembang', 'Malang'] as $city)
                    <a href="{{ route('cinema.cinemas', ['city' => $city]) }}"
                        class="text-xs text-gray-600 hover:text-white px-2.5 py-1
                        border border-white/5 rounded-full hover:border-white/20
                        hover:bg-white/5 transition-all duration-200">
                        {{ $city }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Payment Partners --}}
    <div class="border-t border-white/5 bg-black/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <p
                    class="text-[10px] font-semibold text-gray-600 uppercase tracking-widest
                    flex-shrink-0">
                    Metode Pembayaran:
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    @foreach ([['label' => 'Midtrans', 'icon' => 'ti-credit-card'], ['label' => 'GoPay', 'icon' => 'ti-device-mobile'], ['label' => 'OVO', 'icon' => 'ti-wallet'], ['label' => 'Dana', 'icon' => 'ti-cash'], ['label' => 'BCA', 'icon' => 'ti-building-bank'], ['label' => 'Mandiri', 'icon' => 'ti-building-bank'], ['label' => 'BNI', 'icon' => 'ti-building-bank'], ['label' => 'QRIS', 'icon' => 'ti-qrcode'], ['label' => 'Visa', 'icon' => 'ti-credit-card'], ['label' => 'Mastercard', 'icon' => 'ti-credit-card']] as $payment)
                        <div
                            class="flex items-center gap-1.5 bg-white/5 border border-white/5
                        rounded-lg px-2.5 py-1.5">
                            <i class="ti {{ $payment['icon'] }} text-gray-500 text-xs"></i>
                            <span class="text-[10px] text-gray-500 font-medium">
                                {{ $payment['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">

                <div class="flex items-center gap-4 text-xs text-gray-600">
                    <p>
                        &copy; {{ date('Y') }}
                        <span class="text-gray-500 font-medium">
                            {{ setting('app_name', config('app.name')) }} Cinema
                        </span>.
                        All rights reserved.
                    </p>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <a href="{{ route('pages.privacy') }}"
                        class="text-gray-600 hover:text-gray-400 transition-colors duration-200">
                        Kebijakan Privasi
                    </a>
                    <span class="text-gray-800">·</span>
                    <a href="{{ route('pages.terms') }}"
                        class="text-gray-600 hover:text-gray-400 transition-colors duration-200">
                        Syarat & Ketentuan
                    </a>
                    <span class="text-gray-800">·</span>
                    <a href="{{ route('faqs.index') }}"
                        class="text-gray-600 hover:text-gray-400 transition-colors duration-200">
                        Bantuan
                    </a>
                </div>

            </div>
        </div>
    </div>

</footer>
