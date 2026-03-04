<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('MANV')->unique();
            $table->string('EMAIL')->unique();
            $table->string('HOTEN');
            $table->date('NGAYSINH');
            $table->string('GIOITINH');
            $table->string('CCCD');
            $table->string('SDT')->unique();
            $table->string('CHUCVU');
            $table->date('NGAYVAOLAM');
            $table->integer('TRANGTHAI')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
