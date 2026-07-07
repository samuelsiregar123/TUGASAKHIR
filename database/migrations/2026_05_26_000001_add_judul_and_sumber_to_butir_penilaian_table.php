<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('butir_penilaian', function (Blueprint $table) {
            $table->string('judul_butir')->nullable()->after('pertanyaan');
            $table->string('sumber_acuan')->nullable()->after('judul_butir');
        });
    }

    public function down(): void
    {
        Schema::table('butir_penilaian', function (Blueprint $table) {
            $table->dropColumn(['judul_butir', 'sumber_acuan']);
        });
    }
};
