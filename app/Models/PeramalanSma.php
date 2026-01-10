<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PeramalanSma extends Model
{
    use HasFactory;

    protected $table = 'peramalan_sma';

    protected $fillable = [
        'id_kendaraan',
        'periode_sma',
        'durasi_prediksi',
        'mae',
        'mse',
        'mape',
        'data_peramalan',
    ];

    /**
     * Casting atribut:
     * Mengubah JSON dari database otomatis menjadi Array PHP saat diakses.
     */
    protected $casts = [
        'data_peramalan' => 'array',
        'mae' => 'double',
        'mse' => 'double',
        'mape' => 'double',
    ];

    /**
     * Relasi ke Kendaraan
     */
    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id');
    }
}
