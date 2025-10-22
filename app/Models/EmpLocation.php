<?php

namespace App\Models;

use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Subdistricts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpLocation extends Model
{
    use HasFactory;

    protected $table = "emp_locations";
    protected $fillable = [
        'emp_id',
        'address',
        'province_id',
        'district_id',
        'subdistrict_id',
        'zipcode',
    ];
    public function empBelongtoprovince()
    {
        return $this->belongsTo(Provinces::class, 'province_id', 'id');
    }

    public function empBelongtodistrict()
    {
        return $this->belongsTo(Districts::class, 'district_id', 'id');
    }

    public function empBelongtosubdistrict()
    {
        return $this->belongsTo(Subdistricts::class, 'subdistrict_id', 'id');
    }
}
