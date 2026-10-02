<x-app-layout>
    <x-slot name="header">Kelola Studio</x-slot>

    <div class="p-6 space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([['label' => 'Total Studio', 'value' => $stats['total'], 'icon' => 'ti-door', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'], ['label' => 'Studio Aktif', 'value' => $stats['active'], 'icon' => 'ti-check', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'], ['label' => 'Total Kursi', 'value' => number_format($stats['seats']), 'icon' => 'ti-armchair', 'color' => 'text-violet-600', 'bg' => 'bg-violet-50'], ['label' => 'Bioskop', 'value' => $stats['cinemas'], 'icon' => 'ti-building', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50']] as $stat)
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <div
                            class="w-9 h-9 {{ $stat['bg'] }} rounded-xl flex items-center
                    justify-center">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-lg"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Flash --}}
        @foreach (['success' => 'emerald', 'error' => 'red'] as $type => $color)
            @if (session($type))
                <div class="flex items-center gap-3 bg-{{ $color }}-50 border
        border-{{ $color }}-200 text-{{ $color }}-700 text-sm px-4 py-3 rounded-xl"
                    x-data x-init="setTimeout(() => $el.remove(), 4000)">
                    <i
                        class="ti ti-{{ $type === 'success' ? 'circle-check' : 'alert-circle' }}
            flex-shrink-0"></i>
                    {{ session($type) }}
                </div>
            @endif
        @endforeach

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center
        justify-between gap-3">

            <form method="GET" class="flex flex-wrap gap-2 flex-1">
                {{-- Search --}}
                <div
                    class="flex items-center gap-2 bg-white border border-gray-200
                rounded-xl px-3 h-10 w-56 focus-within:border-gray-400
                transition-colors shadow-sm">
                    <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Nama studio / bioskop..."
                        class="bg-transparent border-none outline-none text-sm
                        text-gray-700 placeholder-gray-400 w-full">
                </div>

                {{-- Cinema filter --}}
                <select name="cinema" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm
                    focus:border-gray-400 min-w-[160px]">
                    <option value="">Semua Bioskop</option>
                    @foreach ($cinemas as $c)
                        <option value="{{ $c->id }}" {{ $cinema == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->city }})
                        </option>
                    @endforeach
                </select>

                {{-- Type filter --}}
                <select name="type" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm">
                    <option value="">Semua Tipe</option>
                    @foreach (['regular' => 'Regular', '3d' => '3D', 'imax' => 'IMAX', '4dx' => '4DX', 'vip' => 'VIP', 'premiere' => 'Premiere'] as $val => $label)
                        <option value="{{ $val }}" {{ $type === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                {{-- Status filter --}}
                <select name="status" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                @if ($search || $cinema || $type || $status)
                    <a href="{{ route('admin.studios.index') }}"
                        class="h-10 px-3 border border-gray-200 text-gray-500 text-sm
                    rounded-xl hover:bg-gray-50 flex items-center gap-1.5
                    transition-all duration-200">
                        <i class="ti ti-x text-sm"></i>
                        Reset
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.studios.create') }}"
                class="inline-flex items-center gap-2 h-10 px-5 bg-gray-900
                hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                transition-all duration-200 shadow-sm flex-shrink-0">
                <i class="ti ti-plus text-base"></i>
                Tambah Studio
            </a>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            @foreach (['Studio', 'Bioskop', 'Tipe', 'Layout', 'Kursi', 'Status', 'Aksi'] as $i => $th)
                                <th
                                    class="px-5 py-3.5 text-xs font-semibold text-gray-500
                            uppercase tracking-wider
                            {{ $i >= 5 ? 'text-center' : 'text-left' }}
                            {{ $i === 6 ? 'text-right' : '' }}">
                                    {{ $th }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($studios as $studio)
                            @php
                                $tc = [
                                    'imax' => ['bg-blue-50 border-blue-100', 'text-blue-700'],
                                    '4dx' => ['bg-purple-50 border-purple-100', 'text-purple-700'],
                                    '3d' => ['bg-cyan-50 border-cyan-100', 'text-cyan-700'],
                                    'vip' => ['bg-amber-50 border-amber-100', 'text-amber-700'],
                                    'premiere' => ['bg-rose-50 border-rose-100', 'text-rose-700'],
                                    'regular' => ['bg-gray-100 border-gray-200', 'text-gray-600'],
                                ][$studio->type] ?? ['bg-gray-100 border-gray-200', 'text-gray-600'];
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors group">

                                {{-- Studio --}}
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $studio->name }}
                                    </p>
                                    @if ($studio->facilities && count($studio->facilities) > 0)
                                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[160px]">
                                            {{ implode(' · ', array_slice($studio->facilities, 0, 2)) }}
                                        </p>
                                    @endif
                                </td>

                                {{-- Bioskop --}}
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.cinema.show', $studio->cinema) }}"
                                        class="text-sm text-gray-700 hover:text-gray-900
                                    font-medium transition-colors">
                                        {{ $studio->cinema->name }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $studio->cinema->city }}
                                    </p>
                                </td>

                                {{-- Tipe --}}
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-block text-xs font-bold px-2.5 py-1
                                rounded-full border {{ $tc[0] }} {{ $tc[1] }}">
                                        {{ strtoupper($studio->type) }}
                                    </span>
                                </td>

                                {{-- Layout --}}
                                <td class="px-5 py-4">
                                    <span class="text-sm text-gray-600">
                                        {{ $studio->rows }} × {{ $studio->cols }}
                                    </span>
                                </td>

                                {{-- Kursi --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ number_format($studio->total_seats) }}
                                        </span>
                                        @if ($studio->total_bookable !== $studio->total_seats)
                                            <span class="text-xs text-gray-400">
                                                ({{ number_format($studio->total_bookable) }} aktif)
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 text-center">
                                    <form method="POST" action="{{ route('admin.studios.toggle', $studio) }}"
                                        class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 text-xs
                                        font-semibold px-3 py-1.5 rounded-full border
                                        transition-all duration-200
                                        {{ $studio->is_active
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                                            : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full
                                        {{ $studio->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}">
                                            </span>
                                            {{ $studio->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-4">
                                    <div
                                        class="flex items-center justify-end gap-1.5
                                opacity-0 group-hover:opacity-100
                                transition-opacity duration-150">
                                        <a href="{{ route('admin.studios.show', $studio) }}"
                                            class="w-8 h-8 bg-gray-100 hover:bg-blue-50
                                        hover:text-blue-600 rounded-lg flex items-center
                                        justify-center text-gray-500
                                        transition-all duration-150">
                                            <i class="ti ti-eye text-sm"></i>
                                        </a>
                                        <a href="{{ route('admin.studios.edit', $studio) }}"
                                            class="w-8 h-8 bg-gray-100 hover:bg-amber-50
                                        hover:text-amber-600 rounded-lg flex items-center
                                        justify-center text-gray-500
                                        transition-all duration-150">
                                            <i class="ti ti-edit text-sm"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.studios.destroy', $studio) }}"
                                            onsubmit="return confirm('Hapus studio {{ $studio->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 bg-gray-100 hover:bg-red-50
                                            hover:text-red-600 rounded-lg flex items-center
                                            justify-center text-gray-500
                                            transition-all duration-150">
                                                <i class="ti ti-trash text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <i class="ti ti-door-off text-4xl text-gray-300 block mb-3"></i>
                                    <p class="text-sm text-gray-400 mb-4">
                                        Belum ada studio
                                    </p>
                                    <a href="{{ route('admin.studios.create') }}"
                                        class="inline-flex items-center gap-2 h-9 px-4
                                    bg-gray-900 text-white text-xs font-semibold
                                    rounded-xl hover:bg-gray-800
                                    transition-all duration-200">
                                        <i class="ti ti-plus text-sm"></i>
                                        Tambah Studio
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($studios->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 flex items-center
            justify-between">
                    <p class="text-xs text-gray-500">
                        {{ $studios->firstItem() }}–{{ $studios->lastItem() }}
                        dari {{ $studios->total() }} studio
                    </p>
                    <div class="flex items-center gap-1.5">
                        @if (!$studios->onFirstPage())
                            <a href="{{ $studios->previousPageUrl() }}"
                                class="w-8 h-8 flex items-center justify-center border
                        border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50
                        transition-colors text-sm">
                                <i class="ti ti-chevron-left"></i>
                            </a>
                        @endif
                        @foreach ($studios->getUrlRange(max(1, $studios->currentPage() - 2), min($studios->lastPage(), $studios->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="w-8 h-8 flex items-center justify-center border rounded-lg
                        text-xs font-medium transition-all duration-150
                        {{ $page === $studios->currentPage()
                            ? 'bg-gray-900 border-gray-900 text-white'
                            : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                                {{ $page }}
                            </a>
                        @endforeach
                        @if ($studios->hasMorePages())
                            <a href="{{ $studios->nextPageUrl() }}"
                                class="w-8 h-8 flex items-center justify-center border
                        border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50
                        transition-colors text-sm">
                                <i class="ti ti-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
