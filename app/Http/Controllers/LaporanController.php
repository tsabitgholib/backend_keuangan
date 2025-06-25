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
        $periodeId = $request->periode_id;
        $start = $request->start_date;
        $end = $request->end_date;

        // Ambil saldo awal
        $saldoAwal = DB::table('saldo_awals')
            ->where('akun_id', $akunId)
            ->where('periode_id', $periodeId)
            ->selectRaw('SUM(CASE WHEN tipe_saldo = "Debit" THEN jumlah ELSE -jumlah END) as saldo_awal')
            ->first();
        $saldoAwalValue = $saldoAwal->saldo_awal ?? 0;

        // Ambil jurnal
        $jurnals = JurnalDetail::where('akun_id', $akunId)
            ->whereHas('jurnal', function ($q) use ($start, $end, $periodeId) {
                $q->whereBetween('tanggal', [$start, $end])
                    ->where('periode_id', $periodeId)
                    ->where('status', 'Diposting');
            })
            ->with('jurnal')
            ->orderBy('jurnal.tanggal')
            ->get();

        // Hitung saldo berjalan
        $saldoBerjalan = $saldoAwalValue;
        $jurnals->each(function ($detail) use (&$saldoBerjalan) {
            $saldoBerjalan += ($detail->debit - $detail->kredit);
            $detail->saldo_berjalan = $saldoBerjalan;
        });

        return response()->json([
            'saldo_awal' => $saldoAwalValue,
            'jurnals' => $jurnals,
            'saldo_akhir' => $saldoBerjalan
        ]);
    }

    /**
     * Menampilkan neraca saldo berdasarkan periode dan level.
     */
    public function neracaSaldo(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        // Ambil semua akun
        $akunQuery = DB::table('akuns')->where('is_active', true);
        if ($level) $akunQuery->where('level', $level);
        $akuns = $akunQuery->get();

        $result = [];
        foreach ($akuns as $akun) {
            // Saldo Awal
            $saldoAwal = DB::table('saldo_awals')
                ->where('akun_id', $akun->id)
                ->where('periode_id', $periode)
                ->selectRaw('SUM(CASE WHEN tipe_saldo = "Debit" THEN jumlah ELSE -jumlah END) as saldo_awal')
                ->first();
            $saldoAwalValue = $saldoAwal->saldo_awal ?? 0;

            // Mutasi Jurnal
            $jurnal = DB::table('jurnal_details')
                ->join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
                ->where('jurnal_details.akun_id', $akun->id)
                ->where('jurnals.periode_id', $periode)
                ->where('jurnals.status', 'Diposting')
                ->selectRaw('SUM(jurnal_details.debit) as total_debit, SUM(jurnal_details.kredit) as total_kredit')
                ->first();
            $totalDebit = $jurnal->total_debit ?? 0;
            $totalKredit = $jurnal->total_kredit ?? 0;

            $saldoAkhir = $saldoAwalValue + ($totalDebit - $totalKredit);

            $result[] = [
                'account_code' => $akun->account_code,
                'account_name' => $akun->account_name,
                'account_type' => $akun->account_type,
                'saldo_awal' => $saldoAwalValue,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'saldo_akhir' => $saldoAkhir
            ];
        }
        return response()->json($result);
    }

    /**
     * Menampilkan posisi keuangan (neraca) berdasarkan periode dan level.
     */
    public function posisiKeuangan(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $asset = $this->getSaldoAkhirByType($periode, 'Asset', $level);
        $kewajiban = $this->getSaldoAkhirByType($periode, 'Kewajiban', $level);
        $ekuitas = $this->getSaldoAkhirByType($periode, 'Ekuitas', $level);
        return response()->json([
            'asset' => $asset,
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
        $pendapatan = $this->getSaldoAkhirByType($periode, 'Pendapatan', $level);
        $beban = $this->getSaldoAkhirByType($periode, 'Beban', $level);
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
     * Helper: Mengambil saldo awal + jurnal berdasarkan tipe dan level.
     */
    private function getSaldoAkhirByType($periodeId, $type, $level = null)
    {
        // Saldo Awal
        $saldoAwalQuery = DB::table('saldo_awals')
            ->join('akuns', 'saldo_awals.akun_id', '=', 'akuns.id')
            ->where('saldo_awals.periode_id', $periodeId)
            ->where('akuns.account_type', $type);
        if ($level) $saldoAwalQuery->where('akuns.level', $level);
        $saldoAwal = $saldoAwalQuery
            ->select(
                'akuns.id',
                'akuns.account_code',
                'akuns.account_name',
                DB::raw('SUM(CASE WHEN saldo_awals.tipe_saldo = "Debit" THEN saldo_awals.jumlah ELSE -saldo_awals.jumlah END) as saldo_awal')
            )
            ->groupBy('akuns.id', 'akuns.account_code', 'akuns.account_name')
            ->get()
            ->keyBy('id');

        // Mutasi Jurnal
        $jurnalQuery = DB::table('jurnal_details')
            ->join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
            ->join('akuns', 'jurnal_details.akun_id', '=', 'akuns.id')
            ->where('jurnals.periode_id', $periodeId)
            ->where('jurnals.status', 'Diposting')
            ->where('akuns.account_type', $type);
        if ($level) $jurnalQuery->where('akuns.level', $level);
        $jurnalSaldo = $jurnalQuery
            ->select(
                'akuns.id',
                'akuns.account_code',
                'akuns.account_name',
                DB::raw('SUM(jurnal_details.debit - jurnal_details.kredit) as saldo_jurnal')
            )
            ->groupBy('akuns.id', 'akuns.account_code', 'akuns.account_name')
            ->get()
            ->keyBy('id');

        // Gabungkan
        $akunIds = $saldoAwal->keys()->merge($jurnalSaldo->keys())->unique();
        $result = collect();
        foreach ($akunIds as $id) {
            $dataAwal = $saldoAwal->get($id);
            $dataJurnal = $jurnalSaldo->get($id);
            $result->push([
                'id' => $id,
                'account_code' => $dataAwal->account_code ?? $dataJurnal->account_code,
                'account_name' => $dataAwal->account_name ?? $dataJurnal->account_name,
                'saldo' => ($dataAwal->saldo_awal ?? 0) + ($dataJurnal->saldo_jurnal ?? 0)
            ]);
        }
        return $result;
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
