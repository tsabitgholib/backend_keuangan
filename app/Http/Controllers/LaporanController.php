<?php

namespace App\Http\Controllers;

use App\Models\JurnalDetail;
use App\Models\Jurnal;
use App\Models\Akun;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Menampilkan data buku besar berdasarkan akun dan rentang tanggal.
     */
    public function bukuBesar(Request $request)
    {
        $akunId = $request->akun_id;
        $start = $request->start_date;
        $end = $request->end_date;
        $data = JurnalDetail::where('akun_id', $akunId)
            ->whereHas('jurnal', function ($q) use ($start, $end) {
                $q->whereBetween('tanggal', [$start, $end])
                    ->where('status', 'Diposting');
            })
            ->with('jurnal')
            ->get();
        return response()->json($data);
    }

    /**
     * Menampilkan neraca saldo berdasarkan periode dan level.
     */
    public function neracaSaldo(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $query = DB::table('jurnal_details')
            ->join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
            ->join('akuns', 'jurnal_details.akun_id', '=', 'akuns.id')
            ->where('jurnals.periode_id', $periode)
            ->where('jurnals.status', 'Diposting');
        if ($level) $query->where('akuns.level', $level);
        $data = $query->select('akuns.account_code', 'akuns.account_name', 'akuns.account_type', DB::raw('SUM(jurnal_details.debit) as total_debit'), DB::raw('SUM(jurnal_details.kredit) as total_kredit'))
            ->groupBy('akuns.id', 'akuns.account_code', 'akuns.account_name', 'akuns.account_type')
            ->get();
        return response()->json($data);
    }

    /**
     * Menampilkan posisi keuangan (neraca) berdasarkan periode dan level.
     */
    public function posisiKeuangan(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $asset = $this->getSaldoByType($periode, 'Asset', $level);
        $kewajiban = $this->getSaldoByType($periode, 'Kewajiban', $level);
        $ekuitas = $this->getSaldoByType($periode, 'Ekuitas', $level);
        return response()->json([
            'asset$asset' => $asset,
            'kewajiban' => $kewajiban,
            'ekuitas' => $ekuitas,
            'total_asset' => $asset->sum('saldo'),
            'total_kewajiban_ekuitas' => $kewajiban->sum('saldo') + $ekuitas->sum('saldo')
        ]);
    }

    /**
     * Menampilkan laporan aktivitas (laba rugi) berdasarkan periode dan level.
     */
    public function aktivitas(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $pendapatan = $this->getSaldoByType($periode, 'Pendapatan', $level);
        $beban = $this->getSaldoByType($periode, 'Beban', $level);
        return response()->json([
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            'total_pendapatan' => $pendapatan->sum('saldo'),
            'total_beban' => $beban->sum('saldo'),
            'laba_bersih' => $pendapatan->sum('saldo') - $beban->sum('saldo')
        ]);
    }

    /**
     * Menampilkan perbandingan saldo dua periode (bulan).
     */
    public function perbandinganBulan(Request $request)
    {
        $periode1 = $request->periode1_id;
        $periode2 = $request->periode2_id;
        $level = $request->level;
        $data1 = $this->getSaldoPerPeriode($periode1, $level);
        $data2 = $this->getSaldoPerPeriode($periode2, $level);
        return response()->json([
            'periode1' => $data1,
            'periode2' => $data2
        ]);
    }

    /**
     * Helper: Mengambil saldo akun berdasarkan tipe dan level.
     */
    private function getSaldoByType($periodeId, $type, $level = null)
    {
        $query = DB::table('jurnal_details')
            ->join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
            ->join('akuns', 'jurnal_details.akun_id', '=', 'akuns.id')
            ->where('jurnals.periode_id', $periodeId)
            ->where('jurnals.status', 'Diposting')
            ->where('akuns.account_type', $type);
        if ($level) $query->where('akuns.level', $level);
        return $query->select('akuns.id', 'akuns.account_code', 'akuns.account_name', DB::raw('SUM(jurnal_details.debit - jurnal_details.kredit) as saldo'))
            ->groupBy('akuns.id', 'akuns.account_code', 'akuns.account_name')
            ->get();
    }

    /**
     * Helper: Mengambil saldo akun per periode dan level.
     */
    private function getSaldoPerPeriode($periodeId, $level = null)
    {
        $query = DB::table('jurnal_details')
            ->join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
            ->join('akuns', 'jurnal_details.akun_id', '=', 'akuns.id')
            ->where('jurnals.periode_id', $periodeId)
            ->where('jurnals.status', 'Diposting');
        if ($level) $query->where('akuns.level', $level);
        return $query->select('akuns.id', 'akuns.account_code', 'akuns.account_name', 'akuns.account_type', DB::raw('SUM(jurnal_details.debit) as total_debit'), DB::raw('SUM(jurnal_details.kredit) as total_kredit'), DB::raw('SUM(jurnal_details.debit - jurnal_details.kredit) as saldo'))
            ->groupBy('akuns.id', 'akuns.account_code', 'akuns.account_name', 'akuns.account_type')
            ->get();
    }
}
