<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->string('target_url')->nullable()->after('tool');
            $table->timestamp('started_at')->nullable()->after('hasil_json');
            $table->timestamp('finished_at')->nullable()->after('started_at');
            $table->text('error_message')->nullable()->after('finished_at');
        });
    }

    public function down(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->dropColumn(['target_url', 'started_at', 'finished_at', 'error_message']);
        });
    }
};
