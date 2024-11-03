<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('nasabahs')->onDelete('cascade');
            $table->string('no_aplikasi')->unique();
            $table->date('tanggal_aplikasi');
            $table->string('nama_ao'); // Agent that created the application
            $table->decimal('jumlah_penghasilan', 15, 2)->nullable(); // Jumlah penghasilan utama
            $table->decimal('jumlah_permohonan', 15, 2)->nullable(); // Jumlah dana yang dimohonkan
            $table->decimal('jumlah_penghasilan_lainnya', 15, 2)->nullable(); // Penghasilan tambahan jika ada
            $table->integer('jangka_waktu')->nullable(); // Durasi waktu yang diajukan
            $table->decimal('maksimal_pembiayaan', 15, 2)->nullable(); // Maksimal dana yang diajukan
            $table->string('tujuan_pembiayaan')->nullable(); // Alasan atau tujuan pembiayaan
            $table->string('status_perkawinan')->nullable(); // Status pernikahan (misalnya: Belum menikah, Menikah, Duda/Janda)
            $table->string('upload_npwp')->nullable();
            $table->string('slip_gaji')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('applications');
    }
}

