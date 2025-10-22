<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpWorkExperiences extends Model
{
    use HasFactory;

    protected $table = "emp_work_experiences";
    protected $fillable = [
        "emp_id",
        "company", //บริษัท str
        "position", //ตำแหน่ง str
        "duration", //ช่วงเวลา str
        "salary", //เงินเดือน int
        "details" //รายละเอียด textarea
    ];
}
