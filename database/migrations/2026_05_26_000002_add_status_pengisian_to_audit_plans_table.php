<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_plans', function (Blueprint $table) {
            $table->enum('status_pengisian', ['proses', 'selesai'])
                  ->default('proses')
                  ->after('waktu_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('audit_plans', function (Blueprint $table) {
            $table->dropColumn('status_pengisian');
        });
    }
};
