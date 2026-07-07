<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->enum('tool', ['curl', 'testssl', 'nmap', 'nikto', 'zap']);
            $table->enum('status', ['menunggu', 'berjalan', 'selesai', 'gagal'])->default('menunggu');
            $table->json('hasil_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};
