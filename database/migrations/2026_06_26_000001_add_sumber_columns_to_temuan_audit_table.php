<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temuan_audit', function (Blueprint $table) {
            if (! Schema::hasColumn('temuan_audit', 'sumber')) {
                $table->enum('sumber', ['otomatis', 'manual'])->default('manual')->after('status_tindak_lanjut');
            }
            if (! Schema::hasColumn('temuan_audit', 'jenis_kelemahan')) {
                $table->json('jenis_kelemahan')->nullable()->after('sumber');
            }
            if (! Schema::hasColumn('temuan_audit', 'is_lengkap')) {
                $table->boolean('is_lengkap')->default(true)->after('jenis_kelemahan');
            }
            if (! Schema::hasColumn('temuan_audit', 'is_aktif')) {
                $table->boolean('is_aktif')->default(true)->after('is_lengkap');
            }
        });
    }

    public function down(): void
    {
        Schema::table('temuan_audit', function (Blueprint $table) {
            $cols = ['is_aktif', 'is_lengkap', 'jenis_kelemahan', 'sumber'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('temuan_audit', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
