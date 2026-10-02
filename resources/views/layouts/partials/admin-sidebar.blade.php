<aside class="w-64 min-w-[256px] bg-white border-r border-gray-100 flex flex-col h-screen sticky top-0">

    {{-- Logo --}}
    <div class="px-5 py-5 border-b border-gray-100 flex items-center gap-3">

        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden bg-gray-900">

            @if (setting('app_logo'))
                <img src="{{ Storage::url(setting('app_logo')) }}" alt="{{ setting('app_name', 'TicketIn') }}"
                    class="w-full h-full object-cover">
            @else
                <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
            @endif

        </div>

        <div>
            <p class="text-sm font-semibold text-gray-900 leading-tight">
                {{ setting('app_name', 'TicketIn') }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">
                Panel Admin
            </p>
        </div>

    </div>

    {{-- Navigasi --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">

        {{-- Utama --}}
        <div>
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">Utama</p>
            <x-admin-nav-link route="admin.dashboard" icon="ti-layout-dashboard" label="Dashboard" />
        </div>

        {{-- Manajemen Event --}}
        <div>
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">Event</p>
            <x-admin-nav-link route="admin.events.index" :active="request()->routeIs('admin.events.*')" icon="ti-calendar-event"
                label="Daftar Event" />
            <x-admin-nav-link route="admin.categories.index" :active="request()->routeIs('admin.categories.*')" icon="ti-tag" label="Kategori" />
            <x-admin-nav-link route="admin.tickets.index" :active="request()->routeIs('admin.tickets.*')" icon="ti-ticket" label="Tiket" />
        </div>

        {{-- Bioskop --}}
        <div>
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">Bioskop</p>
            <x-admin-nav-link route="admin.cinema.index" icon="ti-building" label="Kelola Bioskop" :active="request()->routeIs('admin.cinema.*')" />
            <x-admin-nav-link route="admin.studios.index" icon="ti-door" label="Kelola Studio" :active="request()->routeIs('admin.studios.*')" />
            <x-admin-nav-link route="admin.movies.index" icon="ti-movie" label="Kelola Film" :active="request()->routeIs('admin.movies.*')" />
            <x-admin-nav-link route="admin.showtimes.index" icon="ti-calendar" label="Jadwal Tayang"
                :active="request()->routeIs('admin.showtimes.*')" />
            <x-admin-nav-link route="admin.monitoring.index" icon="ti-activity" label="Live Monitoring"
                :active="request()->routeIs('admin.monitoring.*')" />
            <x-admin-nav-link route="admin.reports.index" icon="ti-chart-bar" label="Laporan Penjualan"
                :active="request()->routeIs('admin.reports.*')" />
        </div>

        {{-- Transaksi & Finansial --}}
        <div class="mt-5">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">Keuangan</p>
            <x-admin-nav-link route="admin.orders.index" :active="request()->routeIs('admin.orders.*')" icon="ti-shopping-cart" label="Pesanan"
                :badge="$pendingOrders ?? 0" />
            <x-admin-nav-link route="admin.payments.index" :active="request()->routeIs('admin.payments.*')" icon="ti-credit-card" label="Pembayaran"
                :badge="$pendingPayments ?? 0" badgeColor="yellow" />
            <x-admin-nav-link route="admin.transactions.index" :active="request()->routeIs('admin.transactions.*')" icon="ti-exchange"
                label="Transaksi" />
            <x-admin-nav-link route="admin.refunds.index" :active="request()->routeIs('admin.refunds.*')" icon="ti-refresh" label="Pengembalian Dana"
                :badge="$pendingRefunds ?? 0" badgeColor="yellow" />
        </div>

        {{-- Konten --}}
        <div>
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">Konten</p>
            <x-admin-nav-link route="admin.blogs.index" :active="request()->routeIs('admin.blogs.*')" icon="ti-news" label="Blog" />
            <x-admin-nav-link route="admin.faqs.index" :active="request()->routeIs('admin.faqs.*')" icon="ti-help-circle" label="FAQ" />
            <x-admin-nav-link route="admin.about.index" :active="request()->routeIs('admin.about.index*')" icon="ti-info-circle" label="Tentang Kami" />
            <x-admin-nav-link route="admin.pages.index" :active="request()->routeIs('admin.pages.*')" icon="ti-file-text"
                label="Kebijakan Privasi" />
        </div>

        {{-- Pengguna --}}
        <div>
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">Pengguna</p>
            <x-admin-nav-link route="admin.users.index" :active="request()->routeIs('admin.users.*')" icon="ti-users" label="User" />
            <x-admin-nav-link route="admin.officers.index" :active="request()->routeIs('admin.officers.*')" icon="ti-id-badge"
                label="Petugas Lapangan" />
        </div>

        {{-- Sistem --}}
        <div>
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-3 mb-2">Sistem</p>
            <x-admin-nav-link route="admin.notifications.index" :active="request()->routeIs('admin.notifications.index*')" icon="ti-bell" label="Notifikasi" />
            <x-admin-nav-link route="admin.settings.index" :active="request()->routeIs('admin.settings.*')" icon="ti-settings" label="Pengaturan" />
        </div>

    </nav>

    {{-- Footer --}}
    <div class="px-3 py-3 border-t border-gray-100">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 cursor-pointer group">
            <div
                class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-400 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </div>

</aside>
