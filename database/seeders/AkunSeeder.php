<?php

// namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;
// use App\Models\Akun;

// class AkunSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//         // Level 1
//         $asset = Akun::create(['account_code' => '1000', 'account_name' => 'Asset', 'level' => 1, 'parent_id' => null, 'account_type' => 'Asset', 'is_active' => true]);
//         $kewajiban = Akun::create(['account_code' => '2000', 'account_name' => 'Kewajiban', 'level' => 1, 'parent_id' => null, 'account_type' => 'Kewajiban', 'is_active' => true]);
//         $ekuitas = Akun::create(['account_code' => '3000', 'account_name' => 'Ekuitas', 'level' => 1, 'parent_id' => null, 'account_type' => 'Ekuitas', 'is_active' => true]);
//         $pendapatan = Akun::create(['account_code' => '4000', 'account_name' => 'Pendapatan', 'level' => 1, 'parent_id' => null, 'account_type' => 'Pendapatan', 'is_active' => true]);
//         $beban = Akun::create(['account_code' => '5000', 'account_name' => 'Beban', 'level' => 1, 'parent_id' => null, 'account_type' => 'Beban', 'is_active' => true]);

//         // Level 2
//         $kas = Akun::create(['account_code' => '1100', 'account_name' => 'Kas & Setara Kas', 'level' => 2, 'parent_id' => $asset->id, 'account_type' => 'Asset', 'is_active' => true]);
//         $piutang = Akun::create(['account_code' => '1200', 'account_name' => 'Piutang Usaha', 'level' => 2, 'parent_id' => $asset->id, 'account_type' => 'Asset', 'is_active' => true]);
//         $hutang = Akun::create(['account_code' => '2100', 'account_name' => 'Hutang Usaha', 'level' => 2, 'parent_id' => $kewajiban->id, 'account_type' => 'Kewajiban', 'is_active' => true]);
//         $modal = Akun::create(['account_code' => '3100', 'account_name' => 'Modal Pemilik', 'level' => 2, 'parent_id' => $ekuitas->id, 'account_type' => 'Ekuitas', 'is_active' => true]);
//         $penjualan = Akun::create(['account_code' => '4100', 'account_name' => 'Penjualan', 'level' => 2, 'parent_id' => $pendapatan->id, 'account_type' => 'Pendapatan', 'is_active' => true]);
//         $gaji = Akun::create(['account_code' => '5100', 'account_name' => 'Beban Gaji', 'level' => 2, 'parent_id' => $beban->id, 'account_type' => 'Beban', 'is_active' => true]);

//         // Level 3
//         Akun::create(['account_code' => '1110', 'account_name' => 'Kas Kecil', 'level' => 3, 'parent_id' => $kas->id, 'account_type' => 'Asset', 'is_active' => true]);
//         Akun::create(['account_code' => '1120', 'account_name' => 'Kas Bank', 'level' => 3, 'parent_id' => $kas->id, 'account_type' => 'Asset', 'is_active' => true]);
//         Akun::create(['account_code' => '1210', 'account_name' => 'Piutang Zakat', 'level' => 3, 'parent_id' => $piutang->id, 'account_type' => 'Asset', 'is_active' => true]);
//         Akun::create(['account_code' => '2110', 'account_name' => 'Hutang Dagang', 'level' => 3, 'parent_id' => $hutang->id, 'account_type' => 'Kewajiban', 'is_active' => true]);
//         Akun::create(['account_code' => '3110', 'account_name' => 'Modal Disetor', 'level' => 3, 'parent_id' => $modal->id, 'account_type' => 'Ekuitas', 'is_active' => true]);
//         Akun::create(['account_code' => '4110', 'account_name' => 'Penjualan Barang', 'level' => 3, 'parent_id' => $penjualan->id, 'account_type' => 'Pendapatan', 'is_active' => true]);
//         Akun::create(['account_code' => '5110', 'account_name' => 'Gaji Karyawan', 'level' => 3, 'parent_id' => $gaji->id, 'account_type' => 'Beban', 'is_active' => true]);
//     }
// }
