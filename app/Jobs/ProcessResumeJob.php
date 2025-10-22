<?php

namespace App\Jobs;

use Filament\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\PdfResumeController; // ใช้ Controller เดิมสำหรับ adjustData/saveToDB
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

/**
 * Job สำหรับประมวลผล Resume Text ที่ดึงมาจาก PDF ผ่าน Gemini API ใน Background
 */
class ProcessResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $resumeText;
    protected string $image;

    public int $tries = 1;
    // // กำหนดเวลาที่ใช้ในการรัน Job (ถ้าเกิน 180s Job จะถูกยกเลิกและพยายามใหม่)
    public int $timeout = 500;

    /**
     * สร้าง Job Instance ใหม่
     */
    public function __construct(string $resumeText, string $image)
    {
        $this->resumeText = $resumeText;
        $this->image = $image;
    }

    /**
     * เมธอดนี้จะถูกเรียกเมื่อ Worker ดึง Job ออกจากคิว
     */
    public function handle(): void
    {
        // ใช้ logic จาก Controller เดิมในการเรียก API แต่รันใน Background Worker
        $this->sendJsonToAi();
    }

    /**
     * ดึง Logic จาก Controller เดิมมาใช้ใน Background Worker เพื่อแก้ปัญหา Timeout
     */
    protected function sendJsonToAi(): void
    {
        $resumeText = $this->resumeText;
        $image = $this->image;

        // 1. กำหนด API Key และ URL
        $apiKey = env('GEMINI_API_KEY');
        $model = 'gemini-2.5-flash';
        $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
        $url = "{$baseUrl}{$model}:generateContent?key={$apiKey}";

        // 2. กำหนด JSON Schema (ใช้ Schema เดิมจาก Controller)
        $jsonSchema = [
            'type' => 'object',
            'properties' => [
                'full_name' => ['type' => 'string', 'description' => 'ชื่อ นามสกุล'],
                'prefix_name' => ['type' => 'string', 'description' => 'คำนำหน้าชื่อ'],
                'name' => ['type' => 'string', 'description' => 'ชื่อของผู้สมัคร'],
                'last_name' => ['type' => 'string', 'description' => 'นามสกุลของผู้สมัคร'],
                'email' => ['type' => 'string', 'description' => 'อีเมลติดต่อ'],
                'tel' => ['type' => 'string', 'description' => 'เบอร์โทรศัพท์มือถือ'],
                'nationality' => ['type' => 'string'],
                'religion' => ['type' => 'string'],
                'date_of_birth' => ['type' => 'string', 'description' => 'วันเกิดในรูปแบบ คริสต์ศักราช (ค.ศ.) YYYY-MM-DD'],
                'weight' => ['type' => 'string', 'description' => 'น้ำหนัก ไม่ต้องใส่หน่วย (ตัวเลขเท่านั้น)'],
                'height' => ['type' => 'string', 'description' => 'ส่วนสูงถ้ามาเป็นหน่วยเมตร ให้แปลเป็น cm แต่ไม่ต้องใส่หน่วย (ตัวเลขเท่านั้น)'],
                'gender' => ['type' => 'string'],
                'marital_status' => ['type' => 'string'],
                'military' => ['type' => 'string', 'description' => 'สถานะการเกณฑ์หทาร ใช้แค่คำว่า ผ่านเกณฑ์หารแล้ว หรือ ยังไม่ผ่านการเกณฑ์หทาร เท่านั้น ถ้าไม่มีก็ null'],
                'address' => ['type' => 'string'],
                'subdistrict' => ['type' => 'string', 'description' => 'ตรวจสอบความถูกต้องกับฐานข้อมูลตำบลก่อนแล้วเอาชื่อที่ถูกต้อง'],
                'district' => ['type' => 'string', 'description' => 'ตรวจสอบความถูกต้องกับฐานข้อมูลอำเภอก่อนแล้วเอาชื่อที่ถูกต้อง'],
                'province' => ['type' => 'string', 'description' => 'ตรวจสอบความถูกต้องกับฐานข้อมูลจังหวัดก่อนแล้วเอาชื่อที่ถูกต้อง'],
                'work_experience' => [
                    'type' => 'array',
                    'description' => 'รายการประสบการณ์ทำงาน (บันทึกลงตาราง work_experiences)',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'company' => ['type' => 'string'],
                            'position' => ['type' => 'string'],
                            'duration' => ['type' => 'string', 'description' => 'ช่วงเวลาที่ทำงานในรูปแบบ คริสต์ศักราช (ค.ศ.) เช่น "2015-07 - 2024-10" หรือ "July 2015 - Present"'],
                            'salary' => ['type' => 'string', 'description' => 'เงินเดือนล่าสุด (ตัวเลขเท่านั้น)'],
                            'details' => ['type' => 'string', 'description' => 'รายละเอียดหน้าที่ความรับผิดชอบ'],
                        ]
                    ]
                ],
                'education' => [
                    'type' => 'array',
                    'description' => 'รายการประวัติการศึกษา (บันทึกลงตาราง educations) หากไม่มีข้อมูลให้ใช้ Array ว่าง []',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'institution' => ['type' => 'string', 'description' => 'ชื่อสถาบันหรือโรงเรียน'],
                            'degree' => ['type' => 'string', 'description' => 'ชื่อวุฒิการศึกษา เช่น วิศวกรรมศาสตร์บัณทิต'],
                            'major' => ['type' => 'string', 'description' => 'สาขาวิชา'],
                            'gpa' => ['type' => 'string', 'description' => 'เกรดเฉลี่ย'],
                            'education_level' => ['type' => 'string', 'description' => 'ระดับการศึกษา เช่น ปริญญาตรี'],
                            'faculty' => ['type' => 'string', 'description' => 'คณะ'],
                            'last_year' => ['type' => 'string', 'description' => 'ปีที่สำเร็จการศึกษา/ปีล่าสุดที่จบ ในรูปแบบ คริสต์ศักราช (ค.ศ.) ตัวเลข 4 หลัก (YYYY)'],
                        ]
                    ]
                ],
                'lang_skill' => [
                    'type' => 'array',
                    'description' => 'รายการทักษะทางภาษา (บันทึกลงตาราง language_skills)',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'language' => ['type' => 'string'],
                            'speaking' => ['type' => 'string', 'description' => 'เช่น ระดับ: ดีเยี่ยม, พอใช้, อื่นๆ'],
                            'reading' => ['type' => 'string'],
                            'writing' => ['type' => 'string'],
                        ]
                    ]
                ],
                'skills' => [
                    'type' => 'array',
                    'description' => 'รายการทักษะทางเทคนิค/เครื่องมือ (บันทึกลงตาราง technical_skills)',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'skill_name' => ['type' => 'string', 'description' => 'ชื่อทักษะหรือเครื่องมือ'],
                            'level' => ['type' => 'string', 'description' => 'ระดับความชำนาญ: สูง, กลาง, พื้นฐาน'],
                        ]
                    ]
                ],
                'other_contacts' => [
                    'type' => 'array',
                    'description' => 'รายการบุคคลอ้างอิง (บันทึกลงตาราง other_contacts)',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'tel' => ['type' => 'string', 'description' => 'เบอร์โทร'],
                            'email' => ['type' => 'string', 'description' => 'อีเมล'],
                        ]
                    ]
                ],
                'availability_date' => [
                    'type' => 'string',
                    'description' => 'วันที่สะดวกเริ่มทำงาน (เช่น YYYY-MM-DD หรือ "ทันที")'
                ],
                'expected_salary' => [
                    'type' => 'string',
                    'description' => 'เงินเดือนที่คาดหวัง (ตัวเลขเท่านั้น)'
                ],
                'desired_positions' => [
                    'type' => 'array',
                    'description' => 'ตำแหน่งงานที่ผู้สมัครต้องการ (สามารถระบุได้มากกว่า 1 ตำแหน่ง) หากไม่มีให้ใช้ Array ว่าง []',
                    'items' => ['type' => 'string', 'description' => 'ชื่อตำแหน่งงานที่ต้องการ']
                ],
                'certificates' => [
                    'type' => 'array',
                    'description' => 'รายการเกียรติบัตรหรือใบประกาศที่ได้รับ หากไม่มีให้ใช้ Array ว่าง []',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'ชื่อใบประกาศ/เกียรติบัตร เช่น CCNA หรือ หลักสูตรที่ผ่านการอบรม'
                            ],
                            'date_obtained' => [
                                'type' => 'string',
                                'description' => 'วันที่ได้รับ (ปี ค.ศ. YYYY หรือ YYYY-MM-DD)'
                            ]
                        ]
                    ]
                ],
            ]
        ];

        // 3. กำหนด Payload (Body)
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => "นี่คือข้อความ resume ของผู้สมัคร:\n\n{$resumeText}
                            \n\nโปรดแปลงข้อมูลนี้ให้เป็น JSON object ที่ถูกต้อง (Valid JSON) ตามโครงสร้างที่กำหนด
                            \n\nอะไรที่ไม่มีข้อมูล ว่าง รวมถึง type number ก็ให้ ว่าง
                            \n\nส่วนคำนำหน้าชื่อเช่น นาย ถ้าไม่มีให้ดูจาก เพศ ถ้าเป็นผู้หญิงให้ใช้นางสาวไปได้เลย
                            \n\nส่วนของ address ไม่มี ให้ถือ province district subdistrict zipcode เป็น ว่าง ตามไปด้วย
                            แล้วช่วยตรวจสอบ syntex ของความเป็น json ให้ดีก่อนส่งข้อมูลมา หลายครั้งคุณชอบลืม } ปิดท้าย ทำให้ json decode ไม่ได้ "
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
                'responseSchema' => $jsonSchema
            ],
            'systemInstruction' => [
                'parts' => [['text' => 'Respond ONLY with the JSON object. Do not add any introductory or concluding text, notes, or markdown formatting (e.g., ```json).']]
            ]
        ];

        // 4. สร้าง Request (ใช้ Buffer Size ที่ใหญ่กว่าเดิม)
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout(120) // Time out 120s 
            ->connectTimeout(20) // Connect timeout 20s
            ->withOptions([
                'curl' => [
                    // 🌟 ตั้งค่า Buffer Size ให้ใหญ่เป็นพิเศษ (512KB) เพื่อแก้ปัญหา JSON ถูกตัดขาด
                    CURLOPT_BUFFERSIZE => 524288,
                ],
            ])->post($url, $payload);

        if (!$response->successful()) {
            //Log::channel('gemini')->debug("API Error: " . $response->status(), $response->json());
            // ถ้าเกิด Error ให้ throw Exception เพื่อให้ Job ถูก Retry
            throw new \Exception("Gemini API call failed with status: " . $response->status());
        }

        // 5. ดึงผลลัพธ์ JSON (ซึ่งตอนนี้ควรเป็น JSON string ที่สะอาด)
        $result = $response->json();
        $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // 6. แปลง JSON string เป็น PHP Array โดยตรง
        $finalJsonArray = json_decode($generatedText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            //Log::channel('gemini')->debug($generatedText);
            Log::channel('gemini')->debug("มีปัญหาซะแล้ว: " . json_last_error_msg() . " Raw Text: " . $generatedText);
            // ถ้า JSON เสียให้ throw Exception เพื่อให้ Job ถูก Retry
            throw new \Exception("JSON Decode failed: " . json_last_error_msg());
        }
        // 7. ส่งต่อข้อมูลไปยัง Logic การบันทึก (อยู่ใน Controller เดิม)
        // เนื่องจาก adjustData และ saveToDB เป็น static เราสามารถเรียกใช้ได้

        $pdfResume = new PdfResumeController();
        $pdfResume->adjustData($finalJsonArray ?? [], $image);
    }
}
