<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','ketua_tim','auditor','auditee','supervisor') NOT NULL DEFAULT 'auditee'");
    }

    public function down(): void
    {
        // Hapus user supervisor dulu agar tidak melanggar constraint
        DB::table('users')->where('role', 'supervisor')->update(['role' => 'auditee']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','ketua_tim','auditor','auditee') NOT NULL DEFAULT 'auditee'");
    }
};
