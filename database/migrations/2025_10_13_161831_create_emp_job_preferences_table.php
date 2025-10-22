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
        Schema::create('emp_job_preferences', function (Blueprint $table) {
            // โค้ดสำหรับใช้ในไฟล์ Migration (database/migrations/...)
            $table->id();
            $table->foreignID('emp_id')->references('id')->on('employees')->onDelete('cascade'); // เชื่อมกับตาราง employees
            $table->string('availability_date')->nullable(); // วันที่สะดวกเริ่มทำงาน (ใช้ string เพื่อความยืดหยุ่นตาม JSON)
            $table->string('expected_salary')->nullable(); // เงินเดือนที่คาดหวัง (ใช้ string เพื่อความยืดหยุ่นตาม JSON)
            $table->string('desired_positions')->nullable(); // ตำแหน่งงานที่ต้องการ (Array ของ String)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_job_preferences');
    }
};
