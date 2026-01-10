<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemakaianKendaraan extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit
     */
    protected $table = 'pemakaian_kendaraan';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'id_kendaraan',
        'bulan',
        'tahun',
        'jumlah_transaksi',
    ];

    /**
     * Relasi: Data pemakaian ini milik satu kendaraan tertentu
     */
    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id');
    }
}
