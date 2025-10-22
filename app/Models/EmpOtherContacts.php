<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpOtherContacts extends Model
{
    use HasFactory;

    protected $table = "emp_other_contacts";
    protected $fillable = [
        'emp_id',
        'name',
        'email',
        'tel',
    ];
}
