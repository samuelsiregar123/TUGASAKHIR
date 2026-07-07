<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_request_id')->constrained('audit_requests')->onDelete('cascade');
            $table->date('waktu_mulai')->nullable();
            $table->date('waktu_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_plans');
    }
};
