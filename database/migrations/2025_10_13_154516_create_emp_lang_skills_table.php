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
        Schema::create('emp_lang_skills', function (Blueprint $table) {
            $table->id(); // (แนะนำ)
            $table->foreignID('emp_id')->references('id')->on('employees')->onDelete('cascade'); // (แนะนำสำหรับตารางลูก)
            $table->string('language')->nullable(); // ภาษา
            $table->string('speaking')->nullable(); // ระดับการพูด
            $table->string('reading')->nullable(); // ระดับการอ่าน
            $table->string('writing')->nullable(); // ระดับการเขียน
            $table->timestamps(); // (แนะนำ)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_lang_skills');
    }
};
