<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temuan_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->foreignId('auditor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('butir_id')->constrained('butir_penilaian')->onDelete('cascade');
            $table->text('deskripsi');
            $table->enum('risiko', ['tinggi', 'sedang', 'rendah']);
            $table->text('rekomendasi');
            $table->enum('status_tindak_lanjut', ['terbuka', 'proses', 'selesai'])->default('terbuka');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temuan_audit');
    }
};
