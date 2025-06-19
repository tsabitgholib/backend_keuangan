<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'keterangan',
        'tipe',
        'periode_id',
        'user_id',
        'nomor_jurnal',
        'status'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status' => 'string'
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasMany(JurnalDetail::class);
    }

    public function getTotalDebitAttribute()
    {
        return $this->detail->sum('debit');
    }

    public function getTotalKreditAttribute()
    {
        return $this->detail->sum('kredit');
    }

    public function isBalanced()
    {
        return $this->total_debit == $this->total_kredit;
    }
}
