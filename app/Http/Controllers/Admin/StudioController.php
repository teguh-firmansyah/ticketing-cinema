<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\SeatLayout;
use App\Models\Studio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudioController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $cinema  = $request->get('cinema', '');
        $type    = $request->get('type', '');
        $status  = $request->get('status', '');

        $query = Studio::with(['cinema'])
            ->withCount([
                'seatLayouts as total_bookable' => fn($q) =>
                $q->where('is_active', true)
                    ->whereNotIn('seat_type', ['blocked'])
            ])
            ->orderBy('cinema_id')
            ->orderBy('order');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('studios.name', 'like', "%{$search}%")
                    ->orWhereHas(
                        'cinema',
                        fn($c) =>
                        $c->where('name', 'like', "%{$search}%")
                    );
            });
        }

        if ($cinema) {
            $query->where('cinema_id', $cinema);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status !== '') {
            $query->where('is_active', $status === 'active');
        }

        $studios = $query->paginate(15)->withQueryString();

        $cinemas = Cinema::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'city']);

        $stats = [
            'total'   => Studio::count(),
            'active'  => Studio::where('is_active', true)->count(),
            'seats'   => Studio::sum('total_seats'),
            'cinemas' => Cinema::whereHas('studios')->count(),
        ];

        return view('admin.studios.index', compact(
            'studios',
            'cinemas',
            'stats',
            'search',
            'cinema',
            'type',
            'status'
        ));
    }

    public function create(Request $request)
    {
        $cinemas        = Cinema::where('is_active', true)
            ->orderBy('city')->orderBy('name')
            ->get(['id', 'name', 'city']);
        $selectedCinema = $request->get('cinema_id')
            ? Cinema::find($request->get('cinema_id'))
            : null;

        return view('admin.studios.create', compact('cinemas', 'selectedCinema'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cinema_id'   => 'required|exists:cinemas,id',
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:regular,3d,imax,4dx,vip,premiere',
            'rows'        => 'required|integer|min:1|max:26',
            'cols'        => 'required|integer|min:1|max:40',
            'facilities'  => 'nullable|array',
            'facilities.*' => 'string',
            'is_active'   => 'boolean',
            'order'       => 'nullable|integer|min:0',
            'vip_rows'    => 'nullable|string',
            'couple_rows' => 'nullable|string',
            'aisle_after' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $studio = Studio::create([
                'cinema_id'   => $validated['cinema_id'],
                'name'        => $validated['name'],
                'type'        => $validated['type'],
                'rows'        => $validated['rows'],
                'cols'        => $validated['cols'],
                'facilities'  => $validated['facilities'] ?? [],
                'is_active'   => $request->boolean('is_active', true),
                'order'       => $validated['order'] ?? 0,
                'total_seats' => 0,
            ]);

            // Parse config layout
            $vipRows    = $this->parseList($validated['vip_rows'] ?? '', 'upper');
            $coupleRows = $this->parseList($validated['couple_rows'] ?? '', 'upper');
            $aisleAfter = $this->parseList($validated['aisle_after'] ?? '', 'int');

            // Generate seat layout
            $this->generateSeatLayout($studio, [
                'rows'        => $validated['rows'],
                'cols'        => $validated['cols'],
                'vip_rows'    => $vipRows,
                'couple_rows' => $coupleRows,
                'aisle_after' => $aisleAfter,
                'skip_seats'  => [],
            ]);

            $studio->syncTotalSeats();

            DB::commit();

            return redirect()
                ->route('admin.studios.show', $studio)
                ->with(
                    'success',
                    "Studio {$studio->name} berhasil dibuat dengan {$studio->total_seats} kursi."
                );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Studio store failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat studio: ' . $e->getMessage());
        }
    }

    public function show(Studio $studio)
    {
        $studio->load([
            'cinema',
            'seatLayouts' => fn($q) => $q->orderBy('row_label')->orderBy('col_number'),
        ]);

        // Seat map grouped by row
        $seatMap = $studio->seatLayouts->groupBy('row_label');

        // Seat type counts
        $seatStats = [
            'total'    => $studio->seatLayouts->count(),
            'regular'  => $studio->seatLayouts->where('seat_type', 'regular')->where('is_active', true)->count(),
            'vip'      => $studio->seatLayouts->where('seat_type', 'vip')->where('is_active', true)->count(),
            'couple'   => $studio->seatLayouts->where('seat_type', 'couple')->where('is_active', true)->count(),
            'blocked'  => $studio->seatLayouts->where('seat_type', 'blocked')->count(),
            'disabled' => $studio->seatLayouts->where('seat_type', 'disabled')->count(),
            'bookable' => $studio->total_seats,
        ];

        // Upcoming showtimes
        $showtimes = $studio->showtimes()
            ->with('movie')
            ->where('status', 'open')
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return view('admin.studios.show', compact(
            'studio',
            'seatMap',
            'seatStats',
            'showtimes'
        ));
    }

    public function edit(Studio $studio)
    {
        $cinemas = Cinema::where('is_active', true)
            ->orderBy('city')->orderBy('name')
            ->get(['id', 'name', 'city']);

        $studio->load('cinema');

        return view('admin.studios.edit', compact('studio', 'cinemas'));
    }

    public function update(Request $request, Studio $studio)
    {
        $validated = $request->validate([
            'cinema_id'   => 'required|exists:cinemas,id',
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:regular,3d,imax,4dx,vip,premiere',
            'facilities'  => 'nullable|array',
            'facilities.*' => 'string',
            'is_active'   => 'boolean',
            'order'       => 'nullable|integer|min:0',
        ]);

        // rows & cols tidak boleh diubah jika sudah ada showtimes
        $hasShowtimes = $studio->showtimes()
            ->whereIn('status', ['open', 'full'])
            ->exists();

        if (!$hasShowtimes) {
            $request->validate([
                'rows' => 'required|integer|min:1|max:26',
                'cols' => 'required|integer|min:1|max:40',
            ]);
        }

        $studio->update([
            'cinema_id'  => $validated['cinema_id'],
            'name'       => $validated['name'],
            'type'       => $validated['type'],
            'facilities' => $validated['facilities'] ?? [],
            'is_active'  => $request->boolean('is_active', true),
            'order'      => $validated['order'] ?? $studio->order,
        ]);

        return redirect()
            ->route('admin.studios.show', $studio)
            ->with('success', 'Studio berhasil diperbarui.');
    }

    public function toggleActive(Studio $studio)
    {
        $studio->update(['is_active' => !$studio->is_active]);

        return back()->with(
            'success',
            "Studio {$studio->name} " .
                ($studio->is_active ? 'diaktifkan.' : 'dinonaktifkan.')
        );
    }

    public function destroy(Studio $studio)
    {
        // Cek active showtime
        if ($studio->showtimes()->whereIn('status', ['open', 'full'])->exists()) {
            return back()->with(
                'error',
                'Studio tidak dapat dihapus karena masih memiliki jadwal aktif.'
            );
        }

        $cinemaId = $studio->cinema_id;
        $name     = $studio->name;

        DB::beginTransaction();
        try {
            SeatLayout::where('studio_id', $studio->id)->delete();
            $studio->delete();
            DB::commit();

            return redirect()
                ->route(
                    'admin.cinema.show',
                    \App\Models\Cinema::find($cinemaId)
                )
                ->with('success', "Studio {$name} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus studio.');
        }
    }

    public function updateSeat(Request $request, Studio $studio)
    {
        $request->validate([
            'seat_layout_id' => 'required|exists:seat_layouts,id',
            'seat_type'      => 'required|in:regular,vip,couple,disabled,blocked',
        ]);

        $seat = SeatLayout::where('id', $request->seat_layout_id)
            ->where('studio_id', $studio->id)
            ->firstOrFail();

        $seat->update([
            'seat_type' => $request->seat_type,
            'is_active' => $request->seat_type !== 'blocked',
        ]);

        $studio->syncTotalSeats();

        return response()->json([
            'success'     => true,
            'seat_number' => $seat->seat_number,
            'seat_type'   => $seat->seat_type,
            'is_active'   => $seat->is_active,
            'total_seats' => $studio->total_seats,
        ]);
    }

    public function regenerateLayout(Request $request, Studio $studio)
    {
        if ($studio->showtimes()->whereIn('status', ['open', 'full'])->exists()) {
            return back()->with(
                'error',
                'Layout tidak dapat di-regenerate karena studio memiliki jadwal aktif.'
            );
        }

        $request->validate([
            'rows'        => 'required|integer|min:1|max:26',
            'cols'        => 'required|integer|min:1|max:40',
            'vip_rows'    => 'nullable|string',
            'couple_rows' => 'nullable|string',
            'aisle_after' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Hapus layout lama
            SeatLayout::where('studio_id', $studio->id)->delete();

            // Update dimensi
            $studio->update([
                'rows' => $request->rows,
                'cols' => $request->cols,
            ]);

            // Generate ulang
            $this->generateSeatLayout($studio, [
                'rows'        => $request->rows,
                'cols'        => $request->cols,
                'vip_rows'    => $this->parseList($request->vip_rows ?? '', 'upper'),
                'couple_rows' => $this->parseList($request->couple_rows ?? '', 'upper'),
                'aisle_after' => $this->parseList($request->aisle_after ?? '', 'int'),
                'skip_seats'  => [],
            ]);

            $studio->syncTotalSeats();

            DB::commit();

            return redirect()
                ->route('admin.studios.show', $studio)
                ->with(
                    'success',
                    "Layout berhasil di-regenerate: {$studio->total_seats} kursi."
                );
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal regenerate layout: ' . $e->getMessage());
        }
    }

    private function generateSeatLayout(Studio $studio, array $cfg): void
    {
        $batch = [];
        for ($r = 0; $r < $cfg['rows']; $r++) {
            $rowLabel = chr(65 + $r);
            for ($c = 1; $c <= $cfg['cols']; $c++) {
                if (in_array($c, $cfg['aisle_after'])) continue;

                $skipCols  = $cfg['skip_seats'][$rowLabel] ?? [];
                $isBlocked = in_array($c, $skipCols);

                $seatType = 'regular';
                if (!$isBlocked) {
                    if (in_array($rowLabel, $cfg['vip_rows']))    $seatType = 'vip';
                    if (in_array($rowLabel, $cfg['couple_rows'])) $seatType = 'couple';
                }

                $batch[] = [
                    'studio_id'   => $studio->id,
                    'seat_number' => $rowLabel . $c,
                    'row_label'   => $rowLabel,
                    'col_number'  => $c,
                    'seat_type'   => $isBlocked ? 'blocked' : $seatType,
                    'is_active'   => !$isBlocked,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }
        foreach (array_chunk($batch, 200) as $chunk) {
            SeatLayout::insert($chunk);
        }
    }

    private function parseList(string $input, string $mode = 'string'): array
    {
        if (empty(trim($input))) return [];
        $parts = array_filter(array_map('trim', explode(',', $input)));
        return match ($mode) {
            'upper' => array_map('strtoupper', $parts),
            'int'   => array_map('intval', $parts),
            default => $parts,
        };
    }
}
