<?php

// namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;
// use App\Models\SaldoAwal;
// use App\Models\Akun;
// use App\Models\Periode;

// class SaldoAwalSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//         $periode = Periode::where('nama', '2024')->first();
//         $kas = Akun::where('account_code', '1100')->first();
//         $bank = Akun::where('account_code', '1200')->first();
//         if ($periode && $kas) {
//             SaldoAwal::create([
//                 'akun_id' => $kas->id,
//                 'periode_id' => $periode->id,
//                 'jumlah' => 10000000,
//                 'tipe_saldo' => 'Debit'
//             ]);
//         }
//         if ($periode && $bank) {
//             SaldoAwal::create([
//                 'akun_id' => $bank->id,
//                 'periode_id' => $periode->id,
//                 'jumlah' => 5000000,
//                 'tipe_saldo' => 'Debit'
//             ]);
//         }
//     }
// }
