<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\EmpSkills;
use App\Models\EmpEducations;
use App\Models\EmpLangSkills;
use App\Models\EmpCertificates;
use App\Models\EmpJobPreference;
use App\Models\EmpOtherContacts;
use App\Models\EmpWorkExperiences;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $table = "employees";
    protected $fillable = [
        'id',
        'prefix_name',
        'name',
        'last_name',
        'email',
        'tel',
        'date_of_birth',
        'marital_status',
        'id_card',
        'gender',
        'height',
        'weight',
        'military', //เกณฑ์หทาร
        'nationality', //สัญชาติ
        'religion', //ศาสนา
        'image',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function age(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->date_of_birth)->age,
        );
    }

    public function getWorkExperienceSummaryAttribute(): string
    {
        // ใช้ map เพื่อจัดรูปแบบข้อมูลแต่ละรายการให้เป็น HTML String
        $summary = $this->empHasmanyWorkExperiences
            ->map(function ($experience) {
                // จัดรูปแบบให้แต่ละ Field ขึ้นบรรทัดใหม่ด้วยแท็ก <br>
                $output = "<B>บริษัท</B>: {$experience->company}<br>";
                $output .= "ตำแหน่ง: {$experience->position}<br>";
                $output .= "ช่วงเวลา: {$experience->duration}<br>";
                $output .= "เงินเดือน: {$experience->salary}<br>";
                $output .= "รายละเอียด: {$experience->details}";

                return $output;
            })
            // ใช้ implode เพื่อแทรกตัวคั่นที่ชัดเจนระหว่างแต่ละประสบการณ์ (---) ด้วยแท็ก <br>
            ->implode("<br>---------------------------<br>");

        // คืนค่าเป็น HTML String (อย่าลืมใช้ ->html() ใน Filament TextColumn)
        return $summary;
    }

    public function empHasonelocation()
    {
        return $this->hasOne(EmpLocation::class, 'emp_id', 'id');
    }

    public function empHasoneJobPreference()
    {
        return $this->hasOne(EmpJobPreference::class, 'emp_id', 'id');
    }

    public function empHasmanyEducation()
    {
        return $this->hasMany(EmpEducations::class, 'emp_id', 'id');
    }

    public function empHasmanyWorkExperiences()
    {
        return $this->hasMany(EmpWorkExperiences::class, 'emp_id', 'id');
    }

    public function empHasmanyLangSkill()
    {
        return $this->hasMany(EmpLangSkills::class, 'emp_id', 'id');
    }

    public function empHasmanySkill()
    {
        return $this->hasMany(EmpSkills::class, 'emp_id', 'id');
    }

    public function empHasmanyCertificate()
    {
        return $this->hasMany(EmpCertificates::class, 'emp_id', 'id');
    }

    public function empHasmanyOtherContact()
    {
        return $this->hasMany(EmpOtherContacts::class, 'emp_id', 'id');
    }
}
