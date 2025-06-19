<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoAwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'akun_id',
        'periode_id',
        'jumlah',
        'tipe_saldo'
    ];

    protected $casts = [
        'jumlah' => 'decimal:2'
    ];

    public function akun()
    {
        return $this->belongsTo(Akun::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function getSaldoAttribute()
    {
        return $this->tipe_saldo === 'Debit' ? $this->jumlah : -$this->jumlah;
    }
}
