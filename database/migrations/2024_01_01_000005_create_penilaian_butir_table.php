<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_butir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->foreignId('auditor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('butir_id')->constrained('butir_penilaian')->onDelete('cascade');
            $table->text('jawaban_auditee')->nullable();
            $table->enum('edk', ['memadai', 'perlu_peningkatan', 'tidak_memadai'])->nullable();
            $table->text('catatan_edk')->nullable();
            $table->enum('eik', ['sesuai', 'tidak_sesuai', 'skip'])->nullable();
            $table->text('catatan_eik')->nullable();
            $table->enum('efk', ['efektif', 'perlu_peningkatan', 'belum_efektif'])->nullable();
            $table->text('catatan_efk')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_butir');
    }
};
