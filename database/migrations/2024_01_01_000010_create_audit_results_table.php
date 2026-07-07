<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->float('nilai_edk_tk')->nullable();
            $table->float('nilai_eik_tk')->nullable();
            $table->float('nilai_efk_tk')->nullable();
            $table->text('konklusi_tk')->nullable();
            $table->float('nilai_edk_mk')->nullable();
            $table->float('nilai_eik_mk')->nullable();
            $table->float('nilai_efk_mk')->nullable();
            $table->text('konklusi_mk')->nullable();
            $table->float('nilai_edk_fk')->nullable();
            $table->float('nilai_eik_fk')->nullable();
            $table->float('nilai_efk_fk')->nullable();
            $table->text('konklusi_fk')->nullable();
            $table->string('path_lhak')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_results');
    }
};
