<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesan_tindak_lanjut', function (Blueprint $table) {
            $table->text('lampiran')->nullable()->after('path_bukti');
        });
    }

    public function down(): void
    {
        Schema::table('pesan_tindak_lanjut', function (Blueprint $table) {
            $table->dropColumn('lampiran');
        });
    }
};
