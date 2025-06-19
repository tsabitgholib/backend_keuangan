<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fitur = [
            'kelola coa',
            'kelola saldo awal',
            'lihat laporan saldo awal',
            'kelola periode',
            'kelola jurnal pemasukan',
            'kelola jurnal pengeluaran',
            'kelola jurnal umum',
            'lihat buku besar',
            'lihat neraca saldo',
            'lihat posisi keuangan',
            'lihat laporan aktivitas',
            'tutup buku',
        ];

        foreach ($fitur as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user = Role::firstOrCreate(['name' => 'user']);

        $admin->syncPermissions($fitur);
        $user->syncPermissions([
            'lihat laporan saldo awal',
            'lihat buku besar',
            'lihat neraca saldo',
            'lihat posisi keuangan',
            'lihat laporan aktivitas',
        ]);
    }
}
