<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditee_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_instansi');
            $table->string('url_target');
            $table->text('daftar_tim')->nullable();
            $table->string('path_nda')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_requests');
    }
};
