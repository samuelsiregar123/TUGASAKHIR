<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bukti_butir', function (Blueprint $table) {
            $table->enum('jenis_acuan', ['edk', 'eik', 'efk'])->default('edk')->after('penilaian_id');
        });
    }

    public function down(): void
    {
        Schema::table('bukti_butir', function (Blueprint $table) {
            $table->dropColumn('jenis_acuan');
        });
    }
};
