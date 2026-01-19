<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use App\Models\Loan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanController extends Controller
{
    // Variabel untuk data umum
    protected $title = 'Peminjaman';
    protected $menu = 'loan';
    protected $directory = 'admin.loan'; // Diubah ke folder view loan

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Menyiapkan array untuk dikirim ke view
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Mengambil data dari database
        $loans = Loan::with(['user', 'item'])->get();

        // Prioritaskan pinjaman yang masih Dipinjam dan jatuh tempo hari ini atau sudah lewat,
        // lalu urutkan berdasarkan tanggal kembali (terdekat dulu).
        $loans = $loans->sortBy(function ($loan) {
            $returnDate = \Carbon\Carbon::parse($loan->return_date);
            $priority = ($loan->status === 'Dipinjam' && $returnDate->lte(\Carbon\Carbon::today())) ? 0 : 1;
            // secondary key: timestamp of return date
            return $priority * 10000000000 + $returnDate->timestamp;
        })->values();

        $data['loans'] = $loans;

        // Me-return view beserta data
        return view($this->directory . '.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Ambil data user yang rolenya siswa
        $data['users'] = User::where('role', 'Siswa')->get();

        // Ambil semua data item
        $data['items'] = Item::all();

        return view($this->directory . '.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi dasar
        $request->validate([
            'user_id' => 'required',
            'loan_date' => 'required|date',
        ]);

        // ======= Pembatasan Jumlah Peminjaman Aktif per User =======
        // Batas maksimum item yang boleh dipinjam aktif adalah 3 unit.
        // Hitung jumlah item yang sedang dipinjam oleh user (status 'Dipinjam').
        $userId = $request->input('user_id');

        $activeItemsCount = (int) \Illuminate\Support\Facades\DB::table('loan_items')
            ->join('loans', 'loan_items.loan_id', '=', 'loans.id')
            ->where('loans.user_id', $userId)
            ->where('loans.status', 'Dipinjam')
            ->sum('loan_items.quantity');

        // Determine requested quantity in this new loan request
        $requestedTotal = 0;
        if ($request->has('items') && is_array($request->input('items')) && count($request->input('items')) > 0) {
            $itemsForCalc = $request->input('items');
            $requestedTotal = array_sum(array_column($itemsForCalc, 'quantity'));
        } else {
            $requestedTotal = (int) $request->input('quantity', 0);
        }

        if (($activeItemsCount + $requestedTotal) > 3) {
            return back()->with([
                'status' => 'danger',
                'title' => 'Batas Peminjaman Tercapai',
                'message' => 'Maaf, Anda sudah mencapai batas maksimal 3 peminjaman. Harap kembalikan barang sebelumnya terlebih dahulu.'
            ])->withInput();
        }
        // =============================================================

        $loanDate = $request->input('loan_date');

        // 2. Pastikan ada item yang dikirim
        if (!$request->has('items') || !is_array($request->input('items'))) {
            return back()->with(['status' => 'danger', 'message' => 'Minimal harus ada satu barang yang dipinjam.'])->withInput();
        }

        $items = $request->input('items');

        // 3. Validasi setiap item dalam array
        foreach ($items as $index => $line) {
            $request->validate([
                "items.$index.item_id" => 'required|exists:items,id',
                "items.$index.quantity" => 'required|integer|min:1',
                "items.$index.return_date" => 'required|date|after_or_equal:loan_date',
            ]);
        }

        // 4. Cek ketersediaan stok & Kondisi Barang
        foreach ($items as $line) {
            $item = Item::findOrFail($line['item_id']);
            if ($item->condition === 'Rusak') {
                return back()->with(['status' => 'danger', 'message' => "Barang {$item->name} rusak."])->withInput();
            }

            $available = $item->availableStockForPeriod($loanDate, $line['return_date']);
            if ($line['quantity'] > $available) {
                return back()->with(['status' => 'danger', 'message' => "Stok {$item->name} tidak cukup (Tersedia: $available)."])->withInput();
            }
        }

        // 5. SOLUSI ERROR 1364: Ambil data dari item pertama untuk memenuhi kolom wajib di tabel loans
        $firstItem = $items[0];
        $totalQty = array_sum(array_column($items, 'quantity'));

        $loan = Loan::create([
            'user_id' => $request->user_id,
            'item_id' => $firstItem['item_id'], // Mengisi field wajib item_id
            'quantity' => $totalQty,             // Mengisi field wajib quantity
            'loan_date' => $loanDate,
            'return_date' => $firstItem['return_date'],
            'status' => 'Dipinjam',
        ]);

        // 6. Simpan ke tabel pivot (loan_items) untuk mendukung multi-item
        $attachData = [];
        foreach ($items as $line) {
            $attachData[$line['item_id']] = [
                'quantity' => $line['quantity'],
                'return_date' => $line['return_date'],
                // Jika tabel pivot Anda punya kolom return_date, masukkan di sini
            ];
        }
        $loan->items()->attach($attachData);

        return redirect()->route('loan.index')->with([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Peminjaman Berhasil Ditambahkan!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // 3. Muat relasi (eager load) ke dalam model $loan yang sudah ada.
        $loan->load(['user', 'items']);
        $data['loan'] = $loan;

        // 4. Kirim semua data ke view.
        return view($this->directory . '.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan)
    {
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Cari data peminjaman berdasarkan ID menggunakan Model Binding
        $data['loan'] = $loan;

        // Ambil data user yang rolenya siswa
        $data['users'] = User::where('role', 'Siswa')->get();

        // Ambil semua data item
        $data['items'] = Item::all();

        // Load existing pivot items for edit form
        $loan->load('items');

        return view($this->directory . '.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        // Support update for multi-item or legacy single-item
        $baseRules = [
            'user_id' => 'required',
            'loan_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:loan_date',
        ];
        $request->validate($baseRules);

        $loanDate = $request->input('loan_date');
        $returnDate = $request->input('return_date');

        $loanData = $request->only(['user_id', 'loan_date', 'return_date']);

        if ($request->has('items') && is_array($request->input('items')) && count($request->input('items')) > 0) {
            $items = $request->input('items');
            foreach ($items as $index => $line) {
                $lineRules = [
                    "items.$index.item_id" => 'required|exists:items,id',
                    "items.$index.quantity" => 'required|integer|min:1',
                ];
                $request->validate($lineRules);
            }

            // Check availability per item excluding this loan's own quantities
            foreach ($items as $line) {
                $item = Item::findOrFail($line['item_id']);
                if ($item->condition === 'Rusak') {
                    return back()->with(['status' => 'danger', 'title' => 'Gagal', 'message' => "Barang {$item->name} dalam kondisi rusak."])->withInput();
                }

                $available = $item->availableStockForPeriod($loanDate, $returnDate, $loan->id);
                if ($line['quantity'] > $available) {
                    return back()->with(['status' => 'danger', 'title' => 'Stok Tidak Cukup', 'message' => "Stok tersedia untuk barang {$item->name} pada periode tersebut hanya: {$available} unit."])->withInput();
                }
            }

            // Update loan data and sync pivot
            // Update loan's summary fields for backward compatibility
            $firstItemId = $items[0]['item_id'] ?? null;
            $totalQty = array_sum(array_column($items, 'quantity'));
            $loan->update(array_merge($loanData, ['item_id' => $firstItemId, 'quantity' => $totalQty]));
            $attach = [];
            foreach ($items as $line) {
                $attach[$line['item_id']] = ['quantity' => $line['quantity']];
            }
            $loan->items()->sync($attach);

            return redirect()->route('loan.index')->with(['status' => 'success', 'title' => 'Berhasil', 'message' => 'Data Peminjaman Berhasil Diubah!']);
        }

        // Legacy single-item update
        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $newItem = Item::findOrFail($validatedData['item_id']);
        if ($newItem->condition === 'Rusak') {
            return redirect()->back()->withInput()->with(['status' => 'danger', 'title' => 'Gagal', 'message' => 'Barang yang dipilih dalam kondisi rusak.']);
        }

        $available = $newItem->availableStockForPeriod($loanDate, $returnDate, $loan->id);
        if ($available < $validatedData['quantity']) {
            return redirect()->back()->withInput()->with(['status' => 'danger', 'title' => 'Stok Tidak Cukup', 'message' => "Stok tersedia untuk periode tersebut hanya: {$available} unit."]);
        }

        // Update loan and pivot
        $loan->update($loanData + ['quantity' => $validatedData['quantity'], 'item_id' => $validatedData['item_id']]);
        $loan->items()->sync([$validatedData['item_id'] => ['quantity' => $validatedData['quantity']]]);

        return redirect()->route('loan.index')->with(['status' => 'success', 'title' => 'Berhasil', 'message' => 'Data Peminjaman Berhasil Diubah!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        // 1. Gunakan Route Model Binding, variabel $loan sudah siap pakai untuk hapus data peminjaman.
        $deleteProcess = $loan->delete();

        // 2. Redirect dengan pesan status berdasarkan hasil proses hapus.
        if ($deleteProcess) {
            return redirect()->route('loan.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Peminjaman Berhasil Dihapus!'
            ]);
        } else {
            return redirect()->route('loan.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Peminjaman Gagal Dihapus!'
            ]);
        }

    }

    public function returnItem(Request $request, Loan $loan)
    {
        // Allow partial returns per item via submitted returned_items[item_id] => quantity
        if ($loan->status == 'Dikembalikan') {
            return back()->with([
                'status' => 'info',
                'title' => 'Informasi',
                'message' => 'Barang ini sudah dikembalikan sebelumnya.'
            ]);
        }

        $data = $request->validate([
            'returned_items' => 'nullable|array',
            'returned_items.*' => 'nullable|integer|min:0'
        ]);

        $returnedItems = $data['returned_items'] ?? [];

        // Load pivot items
        $loan->load('items');

        $anyReturned = false;

        foreach ($loan->items as $item) {
            $pivotQty = (int) $item->pivot->quantity;
            $returned = isset($returnedItems[$item->id]) ? (int) $returnedItems[$item->id] : 0;

            if ($returned <= 0) {
                continue;
            }

            $anyReturned = true;

            // Cap returned to pivot quantity
            $returned = min($returned, $pivotQty);

            $newQty = $pivotQty - $returned;

            if ($newQty >= 0) {
                // Update pivot with new remaining quantity and set return_date for returned portion
                $loan->items()->updateExistingPivot($item->id, [
                    'quantity' => $newQty,
                    'return_date' => now(),
                ]);
            }
        }

        if (!$anyReturned) {
            return back()->with([
                'status' => 'info',
                'title' => 'Tidak Ada Perubahan',
                'message' => 'Tidak ada item yang dikembalikan.'
            ]);
        }

        // Recalculate remaining summary quantity
        $remaining = \Illuminate\Support\Facades\DB::table('loan_items')
            ->where('loan_id', $loan->id)
            ->sum('quantity');

        $loan->quantity = (int) $remaining;

        // If no more pivot items remain, mark loan fully returned
        if ($remaining <= 0) {
            $loan->status = 'Dikembalikan';
            $loan->actual_return_date = now();
        } else {
            $loan->status = 'Dipinjam';
            // keep actual_return_date null if still partially on loan
            $loan->actual_return_date = null;
        }

        $loan->save();

        return back()->with([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Pengembalian barang berhasil diproses.'
        ]);
    }

    public function kembalikan($id)
    {
        $borrow = Loan::find($id); // kalau model kamu namanya Loan, bukan Borrow
        $item = Item::find($borrow->item_id);

        // Tambahkan stok
        // Karena stok sekarang dihitung berdasarkan booking pada periode,
        // kita tidak memodifikasi kolom stock di sini. Hanya update status.
        $borrow->status = 'Dikembalikan';
        $borrow->actual_return_date = now();
        $borrow->save();

        return back()->with('success', 'Barang berhasil dikembalikan.');
    }

    /**
     * Return JSON availability for an item for a given period.
     * Query params: item_id, loan_date, return_date
     */
    public function availability(Request $request)
{
    $item = Item::findOrFail($request->item_id);

    // Hitung stok tersedia: Total Stok - Stok Rusak
    $totalStok = (int) $item->stock;
    $stokRusak = (int) $item->damaged_count;
    $stokFisikTersedia = $totalStok - $stokRusak;

    // Jika Anda menggunakan sistem booking periode:
    $available = $item->availableStockForPeriod($request->loan_date, $request->return_date);

    return response()->json([
        'available' => (int) $available,
        'base_physical' => $stokFisikTersedia
    ]);
}

    /**
     * Reactivate a loan that was previously returned (set status back to 'Dipinjam').
     * This will only succeed if the item is available for the loan period (no overbooking).
     */
    public function reactivate(Loan $loan)
    {
        // Only reactivate loans that are currently marked as 'Dikembalikan'
        if ($loan->status !== 'Dikembalikan') {
            return back()->with([
                'status' => 'info',
                'title' => 'Informasi',
                'message' => 'Hanya peminjaman yang berstatus Dikembalikan yang dapat diaktifkan kembali.'
            ]);
        }

        $item = $loan->item;

        if (!$item) {
            return back()->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Item terkait tidak ditemukan.'
            ]);
        }

        if ($item->condition === 'Rusak') {
            return back()->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Barang dalam kondisi rusak dan tidak dapat diaktifkan kembali.'
            ]);
        }

        // Hitung ketersediaan untuk rentang yang sama, kecualikan loan ini
        $available = 0;
        if (method_exists($item, 'availableStockForPeriod')) {
            $available = $item->availableStockForPeriod($loan->loan_date, $loan->return_date, $loan->id);
        } else {
            $physical = max(0, ($item->stock ?? 0) - ($item->damaged_count ?? 0));
            $overlapping = Loan::where('item_id', $item->id)
                ->where('status', 'Dipinjam')
                ->where(function ($q) use ($loan) {
                    $q->whereDate('return_date', '>=', $loan->loan_date)
                        ->whereDate('loan_date', '<=', $loan->return_date);
                })
                ->where('id', '!=', $loan->id)
                ->sum('quantity');

            $available = max(0, $physical - $overlapping);
        }

        if ($available < $loan->quantity) {
            return back()->with([
                'status' => 'danger',
                'title' => 'Stok Tidak Cukup',
                'message' => "Tidak dapat mengaktifkan kembali. Stok tersedia untuk periode tersebut hanya: {$available} unit."
            ]);
        }

        // Reactivate
        $loan->status = 'Dipinjam';
        $loan->actual_return_date = null;
        $loan->save();

        return back()->with([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Peminjaman berhasil diaktifkan kembali.'
        ]);
    }

    public function exportBulanan(Request $request)
    {
        // 1. Ambil input bulan dan tahun
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // 2. Ambil data dengan relasi items (pivot) sesuai model Loan kamu
        $loans = Loan::with(['user', 'items'])
            ->whereMonth('loan_date', $bulan)
            ->whereYear('loan_date', $tahun)
            ->get();

        // 3. Cek jika data kosong, kembalikan dengan pesan error
        if ($loans->isEmpty()) {
            return back()->with([
                'status' => 'danger',
                'title' => 'Data Kosong',
                'message' => "Tidak ditemukan data peminjaman untuk periode " . date('F', mktime(0, 0, 0, $bulan, 10)) . " $tahun."
            ]);
        }

        // 4. Siapkan nama bulan dalam bahasa Indonesia/Inggris untuk judul
        $namaBulan = \Carbon\Carbon::createFromFormat('m', $bulan)->format('F');

        $data = [
            'loans' => $loans,
            'bulan' => $namaBulan,
            'tahun' => $tahun,
            'tgl_cetak' => now()->format('d F Y')
        ];

        // 5. Load view dan setting kertas
        $pdf = Pdf::loadView('admin.loan.report_pdf', $data);
        $pdf->setPaper('a4', 'landscape'); // Landscape agar tabel yang kolomnya banyak tidak terpotong

        // 6. Tampilkan di browser (Tab Baru)
        return $pdf->stream("laporan-peminjaman-{$bulan}-{$tahun}.pdf");
    }

    /**
     * Display a monthly stock requirement report (HTML) showing
     * each item, physical stock, requested quantity in the month,
     * and any shortfall (recommendation to restock).
     */
    public function stockMonthly(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $start = \Carbon\Carbon::createFromFormat('Y-m-d', "{$tahun}-{$bulan}-01")->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        // Get all items
        $items = Item::all();

        // Compute demand per item: sum of loan_items.quantity for loans that overlap the month
        $demandQuery = \Illuminate\Support\Facades\DB::table('loan_items')
            ->join('loans', 'loan_items.loan_id', '=', 'loans.id')
            ->select('loan_items.item_id', \Illuminate\Support\Facades\DB::raw('SUM(loan_items.quantity) as demand'))
            ->whereDate('loans.loan_date', '<=', $end->toDateString())
            ->whereDate('loans.return_date', '>=', $start->toDateString())
            ->groupBy('loan_items.item_id');

        $demands = $demandQuery->pluck('demand', 'item_id')->toArray();

        $rows = [];
        foreach ($items as $item) {
            $physical = max(0, ($item->stock ?? 0) - ($item->damaged_count ?? 0));
            $demand = isset($demands[$item->id]) ? (int) $demands[$item->id] : 0;
            $shortfall = $demand > $physical ? $demand - $physical : 0;

            $rows[] = [
                'id' => $item->id,
                'name' => $item->name,
                'unique_code' => $item->unique_code,
                'stock' => (int) ($item->stock ?? 0),
                'damaged' => (int) ($item->damaged_count ?? 0),
                'physical' => $physical,
                'demand' => $demand,
                'shortfall' => $shortfall,
            ];
        }

        $data = [
            'rows' => $rows,
            'bulan' => \Carbon\Carbon::createFromFormat('m', $bulan)->format('F'),
            'tahun' => $tahun,
            'start' => $start,
            'end' => $end,
        ];

        return view($this->directory . '.stock_monthly', $data);
    }

}
