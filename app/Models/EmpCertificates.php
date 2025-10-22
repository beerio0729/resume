<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpCertificates extends Model
{
     use HasFactory;

    protected $table = "emp_certificates";
    protected $fillable = [
        "emp_id",
        "name",
        "date_obtained",
    ];
}
