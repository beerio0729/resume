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
        Schema::create('emp_work_experiences', function (Blueprint $table) {
            $table->id(); // (แนะนำให้ใส่เป็น Primary Key)
            $table->foreignID('emp_id')->references('id')->on('employees')->onDelete('cascade'); // (แนะนำสำหรับตารางลูก)
            $table->string('company')->nullable(); // บริษัท
            $table->string('position')->nullable(); // ตำแหน่ง
            $table->string('duration')->nullable(); // ช่วงเวลา
            $table->string('salary')->nullable(); // เงินเดือน (ใช้ string ตามการตกลงเพื่อให้ง่ายต่อการจัดการ JSON)
            $table->text('details')->nullable(); // รายละเอียด (ใช้ text สำหรับ textarea)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_work_experiences');
    }
};
