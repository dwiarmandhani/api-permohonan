<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancingRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('financing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->decimal('total_angsuran_biaya', 15, 2);
            $table->integer('jangka_waktu'); // Duration in months
            $table->string('cabang'); // Branch
            $table->string('capem'); // Capem (Sub-branch)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financing_requests');
    }
}

