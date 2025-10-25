<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dotenv\Util\Str;
use App\Models\Employee;
use App\Models\Districts;
use App\Models\Provinces;
use App\Models\Subdistricts;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Notification;
use App\Jobs\ProcessResumeJob; // 🌟 ต้องเพิ่ม Job Class เข้ามา

class PdfResumeController extends Controller
{
    public int $userID;
    // 🌟 แก้ไข: เมธอด import() จะเรียก Python Server และ Dispatch Job เท่านั้น 🌟
    public function import(array $data, $user)
    {
        $this->userID = $user->id;
        if (!isset($data['attachment']) || empty($data['attachment'])) {
            return;
        }

        // กำหนด path หลักของไฟล์ในเครื่อง
        $basePath = env('AWS_URL_PUBLIC')."/";

        // สร้าง array ของไฟล์ที่ต้องการส่ง
        $files = [];

        foreach ($data['attachment'] as $file) {
            $files[] = $basePath . $file;
        }

        // ส่ง path ทั้งหมดไปยัง Python server (ยังคงเป็น Synchronous call)
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])
            ->post(env('APP_URL').':5000/process-multiple', [
                'files' => $files,
            ]);

        // รับผลลัพธ์ JSON กลับ
        $result = $response->json();
        //dd($result);
        //สร้าง function สำหรับ refresh หน้า หรือ redicrect('')
        if (!empty($result)) {
            foreach ($result as $fileData) {
                if (isset($fileData['text'])) {
                    // 🌟 เปลี่ยน: จากการเรียก self::sendJsonToAi() โดยตรง เป็นการ Dispatch Job 🌟
                    // งานที่ใช้เวลานานจะไปรันใน Background Worker ทันที
                    ProcessResumeJob::dispatch($fileData['text'], $fileData['image'], $this->userID);
                }
            }

            // แจ้งเตือนผู้ใช้ว่างานกำลังประมวลผลอยู่เบื้องหลัง

        }
    }

    // 🌟 เมธอด sendJsonToAi ถูกย้ายไปอยู่ใน App\Jobs\ProcessResumeJob.php แล้ว 🌟
    // เราเหลือเมธอด adjustData และ saveToDB ไว้ที่นี่ เพราะเป็น Logic การจัดการข้อมูล

    public function adjustData(array $data, string $image): void
    {
        // ทดสอบ: Dump JSON String ที่รับเข้ามา เพื่อยืนยันว่าข้อมูลถูกส่งมาถูกต้อง
        //dump($data);
        $hasOneData = [
            // ข้อมูลส่วนบุคคลหลัก
            'full_name' => $data['full_name'] ?? null,
            'prefix_name' => $data['prefix_name'] ?? null,
            'name' => $data['name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
            'image' => $image,
            'tel' => $data['tel'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'religion' => $data['religion'] ?? null,

            // ข้อมูลที่ต้องแปลงค่า (Date/Number)
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'weight' => $data['weight'] ?? null,
            'height' => $data['height'] ?? null,

            // ข้อมูลสถานะและทหาร
            'gender' => $data['gender'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'military' => $data['military'] ?? null,

            // ข้อมูลที่อยู่ (Layer 1 ที่แยก Field ออกมา)
            // 🚨 Fuzzy Match และแปลงชื่อเป็น ID ควรทำที่นี่!
            'address' => $data['address'] ?? null,
            'subdistrict_id' => Subdistricts::where('name_th', $data['subdistrict'])->value('id') ?? null,
            'district_id' => Districts::where('name_th', $data['district'])->value('id') ?? null,
            'province_id' => Provinces::where('name_th', $data['province'])->value('id') ?? null,
            'zipcode' => Subdistricts::where('name_th', $data['subdistrict'])->value('zipcode') ?? null,

            // ข้อมูลความต้องการงาน
            'availability_date' => $data['availability_date'] ?? null,
            'expected_salary' => $data['expected_salary'] ?? null,

            // Desired Positions ต้องแปลง Array เป็น JSON String เพื่อเก็บในตารางหลัก
            'desired_positions' => $data['desired_positions'] ?? [],
        ];

        $hasManyData = [
            'work_experience' => $data['work_experience'] ?? [],
            'education' => $data['education'] ?? [],
            'lang_skill' => $data['lang_skill'] ?? [],
            'skills' => $data['skills'] ?? [],
            'certificates' => $data['certificates'] ?? [],
            'other_contacts' => $data['other_contacts'] ?? [],
        ];

        self::saveToDB($hasOneData, $hasManyData);
        //Log::channel('gemini')->debug(User::find(1)->name);
        Notification::make()
            ->title($data['full_name']. ' เสร็จแล้ว')
            ->sendToDatabase(User::find(1), isEventDispatched: true)
            ->queue('notifications') // ตั้งชื่อคิวให้ชัดเจน
            ->send();
    }

    public function saveToDB(array $hasOneData, array $hasManyData): void
    {
        $emp_create = Employee::create($hasOneData);
        $emp_create->empHasonelocation()->create($hasOneData);
        $emp_create->empHasoneJobPreference()->create($hasOneData);

        $workExperiences = $hasManyData['work_experience'] ?? [];
        $educations = $hasManyData['education'] ?? [];
        $languageSkills = $hasManyData['lang_skill'] ?? [];
        $skills = $hasManyData['skills'] ?? [];
        $certificates = $hasManyData['certificates'] ?? [];
        $otherContacts = $hasManyData['other_contacts'] ?? [];

        if (!empty($workExperiences)) {
            foreach ($workExperiences as $item) {
                $emp_create->empHasmanyWorkExperiences()->create([
                    "company" => $item['company'],
                    "details" =>  $item['details'],
                    "duration" => $item['duration'],
                    "position" => $item['position'],
                    "salary" => $item['salary'],
                ]);
            }
        }

        if (!empty($educations)) {
            foreach ($educations as $item) {
                $emp_create->empHasmanyEducation()->create([
                    'institution' => $item['institution'],       // สถาบัน
                    'degree' => $item['degree'],                 // ชื่อวุฒิการศึกษา
                    'education_level' => $item['education_level'], // ระดับการศึกษา
                    'faculty' => $item['faculty'],               // คณะ
                    'major' => $item['major'],                   // สาขาวิชา
                    'last_year' => $item['last_year'],            // ปีที่สำเร็จการศึกษา
                    'gpa' => (float)$item['gpa'],                // เกรดเฉลี่ย
                ]);
            }
        }

        if (!empty($languageSkills)) {
            foreach ($languageSkills as $item) {
                $emp_create->empHasmanyLangSkill()->create([
                    'language' => $item['language'],
                    'speaking' => $item['speaking'],
                    'reading' => $item['reading'],
                    'writing' => $item['writing'],
                ]);
            }
        }

        if (!empty($skills)) {
            foreach ($skills as $item) {
                $emp_create->empHasmanySkill()->create([
                    'skill_name' => $item['skill_name'],
                    'level' => $item['level'],
                ]);
            }
        }

        if (!empty($certificates)) {
            foreach ($certificates as $item) {
                $emp_create->empHasmanyCertificate()->create([
                    'name' => $item['name'],
                    'date_obtained' => $item['date_obtained'], //ช่วงเวลาที่ได้รับการรับรอง
                ]);
            }
        }

        if (!empty($otherContacts)) {
            foreach ($otherContacts as $item) {
                $emp_create->empHasmanyOtherContact()->create([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'tel' => $item['tel'],
                ]);
            }
        }
    }
}
