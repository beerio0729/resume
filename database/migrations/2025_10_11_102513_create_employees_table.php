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
            $table->string('prefix_name')->nullable();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('tel')->nullable();
            $table->date('date_of_birth')->nullable(); // ใช้ string ตามที่ตกลงเพื่อรองรับ YYYY-MM-DD
            $table->string('marital_status')->nullable();
            $table->string('id_card')->nullable();
            $table->string('gender')->nullable();
            $table->string('height')->nullable(); // ใช้ string ตามที่ตกลง
            $table->string('weight')->nullable(); // ใช้ string ตามที่ตกลง
            $table->string('military')->nullable(); // สถานะการเกณฑ์ทหาร
            $table->string('nationality')->nullable(); // สัญชาติ
            $table->string('religion')->nullable(); // ศาสนา
            $table->string('image')->nullable(); // ชื่อไฟล์/พาทรูปภาพ
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
