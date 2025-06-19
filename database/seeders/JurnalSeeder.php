<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Akun;
use App\Models\Periode;
use App\Models\User;

class JurnalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periode = Periode::where('nama', '2024')->first();
        $user = User::where('email', 'admin@email.com')->first();
        $kas = Akun::where('account_code', '1100')->first();
        $modal = Akun::where('account_code', '3100')->first();
        $gaji = Akun::where('account_code', '5100')->first();
        $penjualan = Akun::where('account_code', '4100')->first();

        // Jurnal 1: Setoran Modal
        if ($periode && $user && $kas && $modal) {
            $jurnal1 = Jurnal::create([
                'tanggal' => '2024-01-05',
                'keterangan' => 'Setoran Modal',
                'tipe' => 'Pemasukan',
                'periode_id' => $periode->id,
                'user_id' => $user->id,
                'nomor_jurnal' => 'JU202401050001',
                'status' => 'Diposting',
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal1->id,
                'akun_id' => $kas->id,
                'debit' => 10000000,
                'kredit' => 0
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal1->id,
                'akun_id' => $modal->id,
                'debit' => 0,
                'kredit' => 10000000
            ]);
        }

        // Jurnal 2: Pembayaran Gaji
        if ($periode && $user && $kas && $gaji) {
            $jurnal2 = Jurnal::create([
                'tanggal' => '2024-01-10',
                'keterangan' => 'Pembayaran Gaji',
                'tipe' => 'Pengeluaran',
                'periode_id' => $periode->id,
                'user_id' => $user->id,
                'nomor_jurnal' => 'JU202401100001',
                'status' => 'Diposting',
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal2->id,
                'akun_id' => $gaji->id,
                'debit' => 2000000,
                'kredit' => 0
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal2->id,
                'akun_id' => $kas->id,
                'debit' => 0,
                'kredit' => 2000000
            ]);
        }
    }
}
