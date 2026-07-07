<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temuan_audit', function (Blueprint $table) {
            $table->string('judul')->nullable()->after('butir_id');
        });

        // Change ENUM to only proses/selesai, update existing 'terbuka' → 'proses'
        DB::statement("UPDATE temuan_audit SET status_tindak_lanjut = 'proses' WHERE status_tindak_lanjut = 'terbuka'");
        DB::statement("ALTER TABLE temuan_audit MODIFY COLUMN status_tindak_lanjut ENUM('proses','selesai') NOT NULL DEFAULT 'proses'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE temuan_audit MODIFY COLUMN status_tindak_lanjut ENUM('terbuka','proses','selesai') NOT NULL DEFAULT 'terbuka'");

        Schema::table('temuan_audit', function (Blueprint $table) {
            $table->dropColumn('judul');
        });
    }
};
