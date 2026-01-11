<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemakaian_kendaraan', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'armscii8';
            $table->collation = 'armscii8_general_ci';

            $table->id();

            $table->foreignId('id_kendaraan')
                ->constrained('kendaraan')
                ->onDelete('cascade');

            $table->tinyInteger('bulan')->comment('1-12');

            $table->integer('tahun');

            $table->integer('jumlah_transaksi')->default(0);

            $table->timestamps();

            $table->unique(['id_kendaraan', 'bulan', 'tahun'], 'unique_pemakaian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemakaian_kendaraan');
    }
};
