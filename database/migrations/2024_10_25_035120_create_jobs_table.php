<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('nasabahs')->onDelete('cascade');
            $table->string('nama_instansi');
            $table->string('no_instansi');
            $table->string('golongan_jabatan');
            $table->string('nip');
            $table->integer('masa_kerja_hari');
            $table->integer('masa_kerja_bulan');
            $table->integer('masa_kerja_tahun');
            $table->string('nama_atasan');
            $table->string('alamat_kantor');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}

