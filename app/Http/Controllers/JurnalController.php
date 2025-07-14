<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JurnalController extends Controller
{
    /**
     * Menampilkan daftar jurnal dengan filter periode/tipe/level jika ada.
     */
    public function index(Request $request)
    {
        $periode = $request->periode_id;
        $tipe = $request->tipe;
        $level = $request->level;
        $query = Jurnal::with(['detail.akun']);
        if ($periode) $query->where('periode_id', $periode);
        if ($tipe) $query->where('tipe', $tipe);
        if ($level) $query->whereHas('detail.akun', fn($q) => $q->where('level', $level));
        return response()->json($query->get());
    }

    /**
     * Menyimpan jurnal baru beserta detailnya.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required',
            'tipe' => 'required|in:Pemasukan,Pengeluaran,Umum',
            'periode_id' => 'required|exists:periodes,id',
            'details' => 'required|array|min:2',
            'details.*.akun_id' => 'required|exists:akuns,id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
        ]);
        // Validasi: hanya satu yang boleh terisi di setiap detail
        foreach ($request->details as $detail) {
            $debit = $detail['debit'] ?? 0;
            $kredit = $detail['kredit'] ?? 0;
            if (($debit > 0 && $kredit > 0) || ($debit == 0 && $kredit == 0)) {
                return response()->json([
                    'error' => 'Setiap detail jurnal hanya boleh memiliki salah satu nilai debit atau kredit yang lebih dari 0, tidak boleh dua-duanya terisi atau dua-duanya nol.'
                ], 422);
            }
        }
        DB::beginTransaction();
        try {
            $jurnal = Jurnal::create([
                'nomor_jurnal' => $this->generateNomorJurnal($request->tipe),
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'tipe' => $request->tipe,
                'periode_id' => $request->periode_id,
                'user_id' => auth()->id(),
                'status' => 'Diposting'
            ]);
            foreach ($request->details as $detail) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id' => $detail['akun_id'],
                    'debit' => $detail['debit'] ?? 0,
                    'kredit' => $detail['kredit'] ?? 0,
                    'keterangan' => $detail['keterangan'] ?? null
                ]);
            }
            DB::commit();
            return response()->json($jurnal, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate nomor jurnal otomatis sesuai tipe.
     */
    private function generateNomorJurnal($tipe)
    {
        $prefix = match ($tipe) {
            'Pemasukan' => 'JM',
            'Pengeluaran' => 'JK',
            'Umum' => 'JU',
            default => 'J'
        };
        $lastJurnal = Jurnal::where('nomor_jurnal', 'like', $prefix . '%')
            ->orderBy('nomor_jurnal', 'desc')
            ->first();
        $lastNumber = $lastJurnal ? intval(substr($lastJurnal->nomor_jurnal, -4)) : 0;
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . date('Ymd') . $newNumber;
    }

    /**
     * Menampilkan detail jurnal tertentu.
     */
    public function show($id)
    {
        $jurnal = Jurnal::with('detail.akun', 'periode', 'user')->findOrFail($id);
        return response()->json($jurnal);
    }

    /**
     * Update data jurnal tertentu beserta detailnya.
     */
    public function update(Request $request, $id)
    {
        $jurnal = Jurnal::findOrFail($id);
        $data = $request->validate([
            'tanggal' => 'sometimes|required|date',
            'keterangan' => 'sometimes|required',
            'tipe' => 'sometimes|required|in:Pemasukan,Pengeluaran,Umum',
            'periode_id' => 'sometimes|required|exists:periodes,id',
            'status' => 'sometimes|required|in:Draft,Diposting,Batal',
            'details' => 'sometimes|array|min:2',
            'details.*.akun_id' => 'required_with:details|exists:akuns,id',
            'details.*.debit' => 'required_with:details|numeric|min:0',
            'details.*.kredit' => 'required_with:details|numeric|min:0',
        ]);
        $jurnal->update($data);
        if (isset($data['details'])) {
            $jurnal->detail()->delete();
            foreach ($data['details'] as $detail) {
                $jurnal->detail()->create($detail);
            }
        }
        return response()->json($jurnal->load('detail.akun', 'periode', 'user'));
    }

    /**
     * Hapus jurnal tertentu beserta detailnya.
     */
    public function destroy($id)
    {
        $jurnal = Jurnal::findOrFail($id);
        $jurnal->detail()->delete();
        $jurnal->delete();
        return response()->json(['message' => 'Jurnal berhasil dihapus']);
    }
}
