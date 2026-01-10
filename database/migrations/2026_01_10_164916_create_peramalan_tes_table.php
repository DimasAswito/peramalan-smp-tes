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
        Schema::create('peramalan_tes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'armscii8';
            $table->collation = 'armscii8_general_ci';
            $table->id(); 
            
            $table->foreignId('id_kendaraan')
                  ->constrained('kendaraan')
                  ->onDelete('cascade');
            
            $table->double('alfa', 8, 4);
            $table->double('beta', 8, 4);
            $table->double('gamma', 8, 4);
            
            $table->integer('durasi_prediksi');
            
            $table->double('mae', 15, 8)->nullable()->default(0);
            $table->double('mse', 15, 8)->nullable()->default(0);
            $table->double('mape', 15, 8)->nullable()->default(0);
            
            $table->json('data_peramalan')->nullable()->comment('Menyimpan detail perhitungan St, Bt, It, Ft');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peramalan_tes');
    }
};
