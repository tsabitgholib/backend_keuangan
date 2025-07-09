<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaldoAwal;
use App\Models\Akun;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaldoAwalController extends Controller
{
    /**
     * Menampilkan daftar saldo awal dengan filter periode/level jika ada.
     */
    public function index(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $query = SaldoAwal::with('akun');
        if ($periode) $query->where('periode_id', $periode);
        if ($level) $query->whereHas('akun', fn($q) => $q->where('level', $level));
        return response()->json($query->get());
    }

    /**
     * Menyimpan saldo awal baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'akun_id' => 'required|exists:akuns,id',
            'periode_id' => 'required|exists:periodes,id',
            'jumlah' => 'required|numeric',
            'tipe_saldo' => 'required|in:Debit,Kredit'
        ]);

        // Validasi: cek apakah sudah pernah input saldo awal (meskipun beda periode)
        $hasAnySaldoAwal = SaldoAwal::exists();
        if ($hasAnySaldoAwal) {
            return response()->json([
                'message' => 'Saldo awal hanya dapat diinput sekali saat sistem pertama kali dijalankan'
            ], 422);
        }

        // Validasi: akun dan periode sudah ada saldo awal?
        $exists = SaldoAwal::where('akun_id', $data['akun_id'])
            ->where('periode_id', $data['periode_id'])
            ->exists();
        if ($exists) {
            return response()->json([
                'message' => 'Akun ini sudah memiliki saldo awal'
            ], 422);
        }
        $saldo = SaldoAwal::create($data);
        return response()->json($saldo, 201);
    }

    /**
     * Menampilkan detail saldo awal tertentu.
     */
    public function show($id)
    {
        $saldo = SaldoAwal::with('akun', 'periode')->findOrFail($id);
        return response()->json($saldo);
    }

    /**
     * Update data saldo awal tertentu.
     */
    public function update(Request $request, $id)
    {
        $saldo = SaldoAwal::findOrFail($id);
        $data = $request->validate([
            'akun_id' => 'sometimes|required|exists:akuns,id',
            'periode_id' => 'sometimes|required|exists:periodes,id',
            'jumlah' => 'sometimes|required|numeric',
            'tipe_saldo' => 'sometimes|required|in:Debit,Kredit'
        ]);
        $saldo->update($data);
        return response()->json($saldo);
    }

    /**
     * Hapus saldo awal tertentu.
     */
    public function destroy($id)
    {
        $saldo = SaldoAwal::findOrFail($id);
        $saldo->delete();
        return response()->json(['message' => 'Saldo awal berhasil dihapus']);
    }

    /**
     * Menampilkan laporan saldo awal berdasarkan filter.
     */
    public function laporan(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $query = DB::table('akuns')
            ->leftJoin('saldo_awals', function ($join) use ($periode) {
                $join->on('akuns.id', '=', 'saldo_awals.akun_id')
                    ->where('saldo_awals.periode_id', '=', $periode);
            });
        if ($level) $query->where('akuns.level', $level);
        $data = $query->select(
            'akuns.account_code',
            'akuns.account_name',
            'akuns.account_type',
            DB::raw('COALESCE(saldo_awals.jumlah, 0) as jumlah'),
            'saldo_awals.tipe_saldo'
        )
            ->orderBy('akuns.account_code')
            ->get();
        return response()->json($data);
    }

    /**
     * Input saldo awal banyak akun sekaligus (looping), debit kredit harus sama.
     */
    public function storeMany(Request $request)
    {
        $data = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'items' => 'required|array|min:2',
            'items.*.akun_id' => 'required|exists:akuns,id',
            'items.*.jumlah' => 'required|numeric',
            'items.*.tipe_saldo' => 'required|in:Debit,Kredit',
        ]);

        // Validasi: cek apakah sudah pernah input saldo awal (meskipun beda periode)
        $hasAnySaldoAwal = SaldoAwal::exists();
        if ($hasAnySaldoAwal) {
            return response()->json([
                'message' => 'Saldo awal hanya dapat diinput sekali saat sistem pertama kali dijalankan'
            ], 422);
        }

        // Validasi: cek duplikasi saldo awal
        $duplikat = [];
        foreach ($data['items'] as $item) {
            $exists = SaldoAwal::where('akun_id', $item['akun_id'])
                ->where('periode_id', $data['periode_id'])
                ->exists();
            if ($exists) {
                $duplikat[] = $item['akun_id'];
            }
        }
        if (count($duplikat) > 0) {
            return response()->json([
                'message' => 'Beberapa akun sudah memiliki saldo awal',
                'akun_id_duplikat' => $duplikat
            ], 422);
        }
        $totalDebit = 0;
        $totalKredit = 0;
        foreach ($data['items'] as $item) {
            if ($item['tipe_saldo'] === 'Debit') {
                $totalDebit += $item['jumlah'];
            } else {
                $totalKredit += $item['jumlah'];
            }
        }
        if ($totalDebit !== $totalKredit) {
            return response()->json(['message' => 'Total debit dan kredit harus sama'], 422);
        }
        $result = [];
        foreach ($data['items'] as $item) {
            $result[] = SaldoAwal::create([
                'akun_id' => $item['akun_id'],
                'periode_id' => $data['periode_id'],
                'jumlah' => $item['jumlah'],
                'tipe_saldo' => $item['tipe_saldo'],
            ]);
        }
        return response()->json(['message' => 'Saldo awal berhasil disimpan', 'data' => $result], 201);
    }
}
