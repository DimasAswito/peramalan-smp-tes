<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PeramalanTes extends Model
{
    use HasFactory;

    protected $table = 'peramalan_tes';

    protected $fillable = [
        'id_kendaraan',
        'alfa',
        'beta',
        'gamma',
        'durasi_prediksi',
        'mae',
        'mse',
        'mape',
        'data_peramalan',
    ];

    /**
     * Casting data JSON agar menjadi Array PHP secara otomatis.
     */
    protected $casts = [
        'data_peramalan' => 'array',
        'alfa' => 'double',
        'beta' => 'double',
        'gamma' => 'double',
        'mae' => 'double',
        'mse' => 'double',
        'mape' => 'double',
    ];

    /**
     * Relasi: Hasil peramalan ini merujuk pada satu kendaraan.
     */
    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id');
    }
}
