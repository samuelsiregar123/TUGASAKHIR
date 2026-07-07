<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_plan_auditors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('peran', ['ketua', 'anggota'])->default('anggota');
            $table->enum('bagian', ['semua', 'tk_mk', 'fk'])->default('semua');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_plan_auditors');
    }
};
