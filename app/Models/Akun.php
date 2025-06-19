<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'level',
        'parent_id',
        'account_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer'
    ];

    public function parent()
    {
        return $this->belongsTo(Akun::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Akun::class, 'parent_id');
    }

    public function saldoAwal()
    {
        return $this->hasMany(SaldoAwal::class, 'akun_id');
    }

    public function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class, 'akun_id');
    }

    public function getNamaLengkapAttribute()
    {
        return $this->account_code . ' - ' . $this->account_name;
    }
}
