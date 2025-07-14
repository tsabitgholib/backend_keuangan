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
     * Helper: Mendapatkan akun berdasarkan level hierarkis
     * Jika level=2, ambil level 1 dan 2
     * Jika level=3, ambil level 1, 2, dan 3
     */
    private function getAkunByHierarchicalLevel($level = null)
    {
        $query = Akun::where('is_active', true);

        if ($level) {
            // Jika level 2, ambil level 1 dan 2
            if ($level == 2) {
                $query->whereIn('level', [1, 2]);
            }
            // Jika level 3, ambil level 1, 2, dan 3
            elseif ($level == 3) {
                $query->whereIn('level', [1, 2, 3]);
            }
            // Jika level 1, ambil hanya level 1
            else {
                $query->where('level', $level);
            }
        }

        return $query->get();
    }

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
        $jurnals = JurnalDetail::join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
            ->where('jurnal_details.akun_id', $akunId)
            ->whereBetween('jurnals.tanggal', [$start, $end])
            ->where('jurnals.periode_id', $periodeId)
            ->where('jurnals.status', 'Diposting')
            ->orderBy('jurnals.tanggal')
            ->select('jurnal_details.*')
            ->with('jurnal')
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
     * Helper untuk membangun tree akun dari array flat
     */
    private function buildAccountTree($akunData, $maxLevel = 3)
    {
        // Index by id
        $akunById = [];
        foreach ($akunData as $a) {
            $akunById[$a['id']] = $a;
            $akunById[$a['id']]['children'] = [];
        }
        // Build tree sesuai maxLevel
        foreach ($akunById as $id => &$akun) {
            if ($akun['level'] == 2 && $akun['parent_id'] && isset($akunById[$akun['parent_id']]) && $maxLevel >= 2) {
                $akunById[$akun['parent_id']]['children'][] = &$akun;
            }
            if ($akun['level'] == 3 && $akun['parent_id'] && isset($akunById[$akun['parent_id']]) && $maxLevel >= 3) {
                $akunById[$akun['parent_id']]['children'][] = &$akun;
            }
        }
        unset($akun);
        // Ambil hanya root (level 1)
        $tree = [];
        foreach ($akunById as $id => $akun) {
            if ($akun['level'] == 1) {
                // Jika maxLevel == 1, kosongkan children
                if ($maxLevel == 1) {
                    $akun['children'] = [];
                }
                // Jika maxLevel == 2, kosongkan children level 2 dari level 2 (tidak ada level 3)
                if ($maxLevel == 2 && !empty($akun['children'])) {
                    foreach ($akun['children'] as &$child) {
                        $child['children'] = [];
                    }
                    unset($child);
                }
                $tree[] = $akun;
            }
        }
        return $tree;
    }

    /**
     * Menampilkan neraca saldo berdasarkan periode dan level.
     */
    public function neracaSaldo(Request $request)
    {
        $periode = $request->periode_id;
        $level = (int)($request->level ?? 3);
        $akuns = Akun::where('is_active', true)
            ->whereIn('level', [1, 2, 3])
            ->orderBy('account_code')
            ->get();
        $akunData = [];
        foreach ($akuns as $akun) {
            $saldoAwal = DB::table('saldo_awals')
                ->where('akun_id', $akun->id)
                ->where('periode_id', $periode)
                ->selectRaw('SUM(CASE WHEN tipe_saldo = "Debit" THEN jumlah ELSE -jumlah END) as saldo_awal')
                ->first();
            $saldoAwalValue = $saldoAwal->saldo_awal ?? 0;
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
            $akunData[$akun->id] = [
                'id' => $akun->id,
                'account_code' => $akun->account_code,
                'account_name' => $akun->account_name,
                'account_type' => $akun->account_type,
                'level' => $akun->level,
                'parent_id' => $akun->parent_id,
                'saldo_awal' => $saldoAwalValue,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'saldo_akhir' => $saldoAkhir,
                'children' => []
            ];
        }
        $result = $this->buildAccountTree($akunData, $level);
        return response()->json($result);
    }

    /**
     * Menampilkan posisi keuangan (neraca) berdasarkan periode dan level.
     */
    public function posisiKeuangan(Request $request)
    {
        $periode = $request->periode_id;
        $level = (int)($request->level ?? 3);
        $types = ['Asset', 'Kewajiban', 'Ekuitas'];
        $result = [];
        foreach ($types as $type) {
            $akuns = Akun::where('is_active', true)
                ->where('account_type', $type)
                ->whereIn('level', [1, 2, 3])
                ->orderBy('account_code')
                ->get();
            $akunData = [];
            foreach ($akuns as $akun) {
                $saldoAwal = DB::table('saldo_awals')
                    ->where('akun_id', $akun->id)
                    ->where('periode_id', $periode)
                    ->selectRaw('SUM(CASE WHEN tipe_saldo = "Debit" THEN jumlah ELSE -jumlah END) as saldo_awal')
                    ->first();
                $saldoAwalValue = $saldoAwal->saldo_awal ?? 0;
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
                $akunData[$akun->id] = [
                    'id' => $akun->id,
                    'account_code' => $akun->account_code,
                    'account_name' => $akun->account_name,
                    'account_type' => $akun->account_type,
                    'level' => $akun->level,
                    'parent_id' => $akun->parent_id,
                    'saldo_awal' => $saldoAwalValue,
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'saldo_akhir' => $saldoAkhir,
                    'children' => []
                ];
            }
            $result[$type] = $this->buildAccountTree($akunData, $level);
        }
        $total_asset = collect($result['Asset'] ?? [])->sum(function ($a) {
            return $a['saldo_akhir'];
        });
        $total_kewajiban = collect($result['Kewajiban'] ?? [])->sum(function ($a) {
            return $a['saldo_akhir'];
        });
        $total_ekuitas = collect($result['Ekuitas'] ?? [])->sum(function ($a) {
            return $a['saldo_akhir'];
        });
        return response()->json([
            'asset' => $result['Asset'] ?? [],
            'kewajiban' => $result['Kewajiban'] ?? [],
            'ekuitas' => $result['Ekuitas'] ?? [],
            'total_asset' => $total_asset,
            'total_kewajiban_ekuitas' => $total_kewajiban + $total_ekuitas
        ]);
    }

    /**
     * Menampilkan laporan aktivitas (laba rugi) berdasarkan periode dan level.
     */
    public function aktivitas(Request $request)
    {
        $periode = $request->periode_id;
        $level = (int)($request->level ?? 3);
        $types = ['Pendapatan', 'Beban'];
        $result = [];
        foreach ($types as $type) {
            $akuns = Akun::where('is_active', true)
                ->where('account_type', $type)
                ->whereIn('level', [1, 2, 3])
                ->orderBy('account_code')
                ->get();
            $akunData = [];
            foreach ($akuns as $akun) {
                $saldoAwal = DB::table('saldo_awals')
                    ->where('akun_id', $akun->id)
                    ->where('periode_id', $periode)
                    ->selectRaw('SUM(CASE WHEN tipe_saldo = "Debit" THEN jumlah ELSE -jumlah END) as saldo_awal')
                    ->first();
                $saldoAwalValue = $saldoAwal->saldo_awal ?? 0;
                $jurnal = DB::table('jurnal_details')
                    ->join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
                    ->where('jurnal_details.akun_id', $akun->id)
                    ->where('jurnals.periode_id', $periode)
                    ->where('jurnals.status', 'Diposting')
                    ->selectRaw('SUM(jurnal_details.debit) as total_debit, SUM(jurnal_details.kredit) as total_kredit')
                    ->first();
                $totalDebit = $jurnal->total_debit ?? 0;
                $totalKredit = $jurnal->total_kredit ?? 0;
                if ($type === 'Pendapatan') {
                    $saldoAkhir = $saldoAwalValue + ($totalKredit - $totalDebit);
                } else {
                    $saldoAkhir = $saldoAwalValue + ($totalDebit - $totalKredit);
                }
                $akunData[$akun->id] = [
                    'id' => $akun->id,
                    'account_code' => $akun->account_code,
                    'account_name' => $akun->account_name,
                    'account_type' => $akun->account_type,
                    'level' => $akun->level,
                    'parent_id' => $akun->parent_id,
                    'saldo_awal' => $saldoAwalValue,
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'saldo_akhir' => $saldoAkhir,
                    'children' => []
                ];
            }
            $result[$type] = $this->buildAccountTree($akunData, $level);
        }
        $total_pendapatan = collect($result['Pendapatan'] ?? [])->sum(function ($a) {
            return $a['saldo_akhir'];
        });
        $total_beban = collect($result['Beban'] ?? [])->sum(function ($a) {
            return $a['saldo_akhir'];
        });
        return response()->json([
            'pendapatan' => $result['Pendapatan'] ?? [],
            'beban' => $result['Beban'] ?? [],
            'total_pendapatan' => $total_pendapatan,
            'total_beban' => $total_beban,
            'laba_bersih' => $total_pendapatan - $total_beban
        ]);
    }

    /**
     * Menampilkan perbandingan saldo dua periode (bulan).
     */
    public function perbandinganPeriode(Request $request)
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
     * Helper: Mengambil saldo akun per periode dan level hierarkis.
     */
    private function getSaldoPerPeriode($periodeId, $level = null)
    {
        // Dapatkan akun berdasarkan level hierarkis
        $akuns = $this->getAkunByHierarchicalLevel($level);
        $akunIds = $akuns->pluck('id')->toArray();

        if (empty($akunIds)) {
            return collect();
        }

        $query = DB::table('jurnal_details')
            ->join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
            ->join('akuns', 'jurnal_details.akun_id', '=', 'akuns.id')
            ->where('jurnals.periode_id', $periodeId)
            ->where('jurnals.status', 'Diposting')
            ->whereIn('akuns.id', $akunIds);

        return $query->select(
            'akuns.id',
            'akuns.account_code',
            'akuns.account_name',
            'akuns.account_type',
            'akuns.level',
            'akuns.parent_id',
            DB::raw('SUM(jurnal_details.debit) as total_debit'),
            DB::raw('SUM(jurnal_details.kredit) as total_kredit'),
            DB::raw('SUM(jurnal_details.debit - jurnal_details.kredit) as saldo')
        )
            ->groupBy('akuns.id', 'akuns.account_code', 'akuns.account_name', 'akuns.account_type', 'akuns.level', 'akuns.parent_id')
            ->orderBy('akuns.account_code')
            ->get();
    }

    // WEB TES VIEW

    public function bukuBesarWeb(Request $request)
    {
        $akunId = $request->akun_id;
        $periodeId = $request->periode_id;
        $start = $request->start_date;
        $end = $request->end_date;
        $data = null;
        if ($akunId && $periodeId && $start && $end) {
            $saldoAwal = DB::table('saldo_awals')
                ->where('akun_id', $akunId)
                ->where('periode_id', $periodeId)
                ->selectRaw('SUM(CASE WHEN tipe_saldo = "Debit" THEN jumlah ELSE -jumlah END) as saldo_awal')
                ->first();
            $saldoAwalValue = $saldoAwal->saldo_awal ?? 0;
            $jurnals = JurnalDetail::join('jurnals', 'jurnal_details.jurnal_id', '=', 'jurnals.id')
                ->where('jurnal_details.akun_id', $akunId)
                ->whereBetween('jurnals.tanggal', [$start, $end])
                ->where('jurnals.periode_id', $periodeId)
                ->where('jurnals.status', 'Diposting')
                ->orderBy('jurnals.tanggal')
                ->select('jurnal_details.*', 'jurnals.tanggal as jurnal_tanggal')
                ->get();
            $saldoBerjalan = $saldoAwalValue;
            foreach ($jurnals as $detail) {
                $saldoBerjalan += ($detail->debit - $detail->kredit);
                $detail->saldo_berjalan = $saldoBerjalan;
            }
            $data = [
                'saldo_awal' => $saldoAwalValue,
                'jurnals' => $jurnals,
                'saldo_akhir' => $saldoBerjalan
            ];
        }
        return view('buku-besar', [
            'data' => $data,
            'akunId' => $akunId,
            'periodeId' => $periodeId,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function neracaSaldoWeb(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $data = null;
        if ($periode) {
            // Ambil akun berdasarkan level hierarkis
            $akuns = $this->getAkunByHierarchicalLevel($level);

            $result = [];
            foreach ($akuns as $akun) {
                $saldoAwal = DB::table('saldo_awals')
                    ->where('akun_id', $akun->id)
                    ->where('periode_id', $periode)
                    ->selectRaw('SUM(CASE WHEN tipe_saldo = "Debit" THEN jumlah ELSE -jumlah END) as saldo_awal')
                    ->first();
                $saldoAwalValue = $saldoAwal->saldo_awal ?? 0;
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
                    'level' => $akun->level,
                    'parent_id' => $akun->parent_id,
                    'saldo_awal' => $saldoAwalValue,
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'saldo_akhir' => $saldoAkhir
                ];
            }

            // Urutkan berdasarkan account_code untuk hierarki yang rapi
            usort($result, function ($a, $b) {
                return strcmp($a['account_code'], $b['account_code']);
            });

            $data = $result;
        }
        return view('neraca-saldo', [
            'data' => $data,
            'periode' => $periode,
            'level' => $level,
        ]);
    }

    public function posisiKeuanganWeb(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $data = null;
        if ($periode) {
            $asset = $this->getSaldoAkhirByType($periode, 'Asset', $level);
            $kewajiban = $this->getSaldoAkhirByType($periode, 'Kewajiban', $level);
            $ekuitas = $this->getSaldoAkhirByType($periode, 'Ekuitas', $level);
            $data = [
                'asset' => $asset,
                'kewajiban' => $kewajiban,
                'ekuitas' => $ekuitas,
                'total_asset' => $asset->sum('saldo'),
                'total_kewajiban_ekuitas' => $kewajiban->sum('saldo') + $ekuitas->sum('saldo')
            ];
        }
        return view('posisi-keuangan', [
            'data' => $data,
            'periode' => $periode,
            'level' => $level,
        ]);
    }

    public function aktivitasWeb(Request $request)
    {
        $periode = $request->periode_id;
        $level = $request->level;
        $data = null;
        if ($periode) {
            $pendapatan = $this->getSaldoAkhirByType($periode, 'Pendapatan', $level);
            $beban = $this->getSaldoAkhirByType($periode, 'Beban', $level);
            $data = [
                'pendapatan' => $pendapatan,
                'beban' => $beban,
                'total_pendapatan' => $pendapatan->sum('saldo'),
                'total_beban' => $beban->sum('saldo'),
                'laba_bersih' => $pendapatan->sum('saldo') - $beban->sum('saldo')
            ];
        }
        return view('aktivitas', [
            'data' => $data,
            'periode' => $periode,
            'level' => $level,
        ]);
    }

    public function perbandinganBulanWeb(Request $request)
    {
        $periode1 = $request->periode1_id;
        $periode2 = $request->periode2_id;
        $level = $request->level;
        $data = null;
        if ($periode1 && $periode2) {
            $data1 = $this->getSaldoPerPeriode($periode1, $level);
            $data2 = $this->getSaldoPerPeriode($periode2, $level);
            $data = [
                'periode1' => $data1,
                'periode2' => $data2
            ];
        }
        return view('perbandingan-bulan', [
            'data' => $data,
            'periode1' => $periode1,
            'periode2' => $periode2,
            'level' => $level,
        ]);
    }
}
