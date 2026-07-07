<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('butir_penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->enum('bagian', ['tk', 'mk', 'fk']);
            $table->unsignedSmallInteger('nomor');
            $table->string('domain')->nullable();
            $table->text('pertanyaan');
            $table->text('rujukan_mk')->nullable();
            $table->boolean('ada_scan')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('butir_penilaian');
    }
};
