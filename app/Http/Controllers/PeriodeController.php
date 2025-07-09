<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periode;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\SaldoAwal;
use App\Models\Akun;
use App\Models\JurnalDetail;

class PeriodeController extends Controller
{
    /**
     * Menampilkan daftar periode.
     */
    public function index()
    {
        $data = Periode::all();
        return response()->json([
            'message' => 'Daftar periode berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * Menyimpan periode baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'status' => 'required|in:Aktif,Tutup'
        ]);

        // Validasi: jika status Aktif, cek apakah sudah ada periode aktif
        if ($data['status'] === 'Aktif') {
            $activePeriode = Periode::where('status', 'Aktif')->exists();
            if ($activePeriode) {
                return response()->json([
                    'message' => 'Hanya boleh ada satu periode aktif'
                ], 422);
            }
        }

        $periode = Periode::create($data);
        return response()->json([
            'message' => 'Periode berhasil ditambahkan',
            'data' => $periode
        ], 201);
    }

    /**
     * Menampilkan detail periode tertentu.
     */
    public function show($id)
    {
        $periode = Periode::findOrFail($id);
        return response()->json($periode);
    }

    /**
     * Update data periode tertentu.
     */
    public function update(Request $request, $id)
    {
        $periode = Periode::findOrFail($id);
        $data = $request->validate([
            'nama' => 'sometimes|required',
            'tanggal_mulai' => 'sometimes|required|date',
            'tanggal_selesai' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Aktif,Tutup'
        ]);

        // Validasi: jika mengubah status menjadi Aktif
        if (isset($data['status']) && $data['status'] === 'Aktif') {
            // Cek apakah periode ini sebelumnya sudah ditutup
            if ($periode->status === 'Tutup') {
                return response()->json([
                    'message' => 'Periode yang sudah ditutup tidak dapat diaktifkan kembali'
                ], 422);
            }

            // Cek apakah sudah ada periode aktif lain
            $activePeriode = Periode::where('status', 'Aktif')
                ->where('id', '!=', $id)
                ->exists();
            if ($activePeriode) {
                return response()->json([
                    'message' => 'Hanya boleh ada satu periode aktif'
                ], 422);
            }
        }

        $periode->update($data);
        return response()->json($periode);
    }

    /**
     * Hapus periode tertentu.
     */
    public function destroy($id)
    {
        $periode = Periode::findOrFail($id);
        $periode->delete();
        return response()->json(['message' => 'Periode berhasil dihapus']);
    }

    /**
     * Menutup periode dan transfer saldo ke periode berikutnya
     */
    public function tutup(Request $request, $id)
    {
        $periode = Periode::findOrFail($id);

        // Cari periode berikutnya
        $periodeBerikutnya = Periode::where('tanggal_mulai', '>', $periode->tanggal_selesai)
            ->orderBy('tanggal_mulai')
            ->first();

        if (!$periodeBerikutnya) {
            return response()->json([
                'error' => 'Periode berikutnya belum dibuat. Buat periode berikutnya terlebih dahulu.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Ambil semua akun aktif tipe Neraca saja
            $akuns = Akun::where('is_active', true)
                ->whereIn('account_type', ['Asset', 'Kewajiban', 'Ekuitas'])
                ->get();

            foreach ($akuns as $akun) {
                // Saldo awal
                $saldoAwal = SaldoAwal::where('akun_id', $akun->id)
                    ->where('periode_id', $periode->id)
                    ->get()
                    ->sum(function ($s) {
                        return $s->tipe_saldo === 'Debit' ? $s->jumlah : -$s->jumlah;
                    });

                // Mutasi jurnal
                $saldoJurnal = JurnalDetail::where('akun_id', $akun->id)
                    ->whereHas('jurnal', function ($q) use ($periode) {
                        $q->where('periode_id', $periode->id)
                            ->where('status', 'Diposting');
                    })
                    ->get()
                    ->sum(function ($d) {
                        return $d->debit - $d->kredit;
                    });

                $saldoAkhir = $saldoAwal + $saldoJurnal;

                if ($saldoAkhir != 0) {
                    $tipeSaldo = $saldoAkhir > 0 ? 'Debit' : 'Kredit';
                    $jumlah = abs($saldoAkhir);

                    // Hapus saldo awal yang mungkin sudah ada
                    SaldoAwal::where('akun_id', $akun->id)
                        ->where('periode_id', $periodeBerikutnya->id)
                        ->delete();

                    // Buat saldo awal baru
                    SaldoAwal::create([
                        'akun_id' => $akun->id,
                        'periode_id' => $periodeBerikutnya->id,
                        'jumlah' => $jumlah,
                        'tipe_saldo' => $tipeSaldo,
                    ]);
                }
            }

            // Tutup periode
            $periode->update(['status' => 'Tutup']);

            DB::commit();

            return response()->json([
                'message' => 'Periode berhasil ditutup dan saldo telah ditransfer ke periode berikutnya',
                'periode_berikutnya' => $periodeBerikutnya->nama
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Gagal menutup periode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengaktifkan periode tertentu
     */
    public function activate($id)
    {
        $periode = Periode::findOrFail($id);

        // Cek apakah periode ini sudah ditutup
        if ($periode->status === 'Tutup') {
            return response()->json([
                'message' => 'Periode yang sudah ditutup tidak dapat diaktifkan kembali'
            ], 422);
        }

        // Cek apakah sudah ada periode aktif
        $activePeriode = Periode::where('status', 'Aktif')
            ->where('id', '!=', $id)
            ->first();
        if ($activePeriode) {
            return response()->json([
                'message' => 'Hanya boleh ada satu periode aktif. Periode ' . $activePeriode->nama . ' masih aktif'
            ], 422);
        }

        $periode->update(['status' => 'Aktif']);
        return response()->json([
            'message' => 'Periode berhasil diaktifkan',
            'data' => $periode
        ]);
    }
}
