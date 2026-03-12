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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Cột ID tự tăng
            $table->string('name'); // Cột Name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Chỉ cần xóa bảng users vì up() chỉ tạo bảng users
        Schema::dropIfExists('users');
    }
};