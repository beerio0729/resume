<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpEducations extends Model
{
    use HasFactory;

    protected $table = "emp_educations";
    protected $fillable = [
        'id',
        'emp_id',
        'institution', //สถาบัน
        'degree',//ชื่อวุฒิการศึกษา เช่น วิศวกรรมศาสตร์บัณทิต
        'education_level', //ระดับการศึกษา เช่น ปริญญาตรี
        'faculty', //คณะ
        'major', //สาขาวิชา
        'start_year', //ปีที่เข้าศึกษา
        'last_year', //ปีที่เข้าศึกษา
        'gpa', //เกรดเฉลี่ย
    ];
}
