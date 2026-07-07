<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_results', function (Blueprint $table) {
            $table->string('konklusi_keseluruhan')->nullable()->after('konklusi_fk');
        });
    }

    public function down(): void
    {
        Schema::table('audit_results', function (Blueprint $table) {
            $table->dropColumn('konklusi_keseluruhan');
        });
    }
};
