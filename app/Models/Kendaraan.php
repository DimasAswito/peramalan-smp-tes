<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kendaraan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang didefinisikan secara eksplisit.
     */
    protected $table = 'kendaraan';

    /**
     * Atribut yang dapat diisi (Mass Assignment).
     */
    protected $fillable = [
        'nama_kendaraan',
    ];

    /**
     * Relasi: Satu kendaraan memiliki banyak data pemakaian.
     * (Kita akan buat model PemakaianKendaraan setelah ini)
     */
    public function pemakaian()
    {
        return $this->hasMany(PemakaianKendaraan::class, 'id_kendaraan', 'id');
    }
}
