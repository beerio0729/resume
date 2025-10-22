<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpLangSkills extends Model
{
    use HasFactory;

    protected $table = "emp_lang_skills";
    protected $fillable = [
        "language",
        "speaking",
        "reading",
        "writing"
    ];
}
