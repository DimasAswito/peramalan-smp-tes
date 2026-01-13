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
        Schema::create('perbandingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kendaraan')->constrained('kendaraan')->onDelete('cascade');
            // SMA Params
            $table->integer('periode_sma');
            // TES Params
            $table->double('alpha');
            $table->double('beta');
            $table->double('gamma');
            // General
            $table->integer('durasi_prediksi');

            // Metrics SMA
            $table->double('mae_sma');
            $table->double('mse_sma');
            $table->double('mape_sma');

            // Metrics TES
            $table->double('mae_tes');
            $table->double('mse_tes');
            $table->double('mape_tes');

            // Result
            $table->string('metode_terbaik'); // 'SMA' or 'TES'
            $table->text('data_perbandingan'); // JSON for table/charts (optional but good for detail)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perbandingan');
    }
};
