<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpSkills extends Model
{
    use HasFactory;

    protected $table = "emp_skills";
    protected $fillable = [
        'emp_id',
        'skill_name',
        'level',
    ];
}
