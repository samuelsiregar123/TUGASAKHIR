<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('butir_penilaian', function (Blueprint $table) {
            $table->text('acuan_edk')->nullable()->after('ada_scan');
            $table->text('acuan_eik')->nullable()->after('acuan_edk');
            $table->text('acuan_efk')->nullable()->after('acuan_eik');
        });
    }

    public function down(): void
    {
        Schema::table('butir_penilaian', function (Blueprint $table) {
            $table->dropColumn(['acuan_edk', 'acuan_eik', 'acuan_efk']);
        });
    }
};
