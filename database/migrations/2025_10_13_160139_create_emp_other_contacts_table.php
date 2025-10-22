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
        Schema::create('emp_other_contacts', function (Blueprint $table) {
            $table->id(); // (แนะนำ)
            $table->foreignID('emp_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('name')->nullable(); // ชื่อบุคคลอ้างอิง/ผู้ติดต่อ
            $table->string('email')->nullable(); // อีเมลติดต่อ
            $table->string('tel')->nullable(); // เบอร์โทรศัพท์
            $table->timestamps(); // (แนะนำ)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_other_contacts');
    }
};
