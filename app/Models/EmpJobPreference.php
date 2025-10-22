<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpJobPreference extends Model
{
    use HasFactory;

    protected $table = "emp_job_preferences";
    protected $fillable = [
        "emp_id",
        "availability_date",
        "expected_salary",
        "desired_positions", //เป็น array
    ];
    
    protected $casts = [
        'desired_positions' => 'array', 
    ];
}
