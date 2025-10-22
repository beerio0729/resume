<?php

namespace App\Filament\Resources\Employees\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Notification;
use Filament\Forms\Components\fileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Http\Controllers\PdfResumeController;
use App\Filament\Resources\Employees\EmployeeResource;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Component as Livewire;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    // 💡 1. เปิดใช้งาน Polling เพื่อให้ตารางรีเฟรชตัวเองทุก 10 วินาที
    // เมื่อ Job อัปเดต DB เสร็จ ตารางจะแสดงข้อมูลใหม่ทันที
    protected function getTablePollingInterval(): ?string
    {
        return '5s';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('upLoadPDF')
                ->label('อับโหลด Resume')
                ->color('info')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->schema([
                    fileUpload::make('attachment')
                        ->label('อับโหลดไฟล์ได้พร้อมกันมากกกว่า 1 ไฟล์')
                        ->multiple()
                        ->disk('resume_storage')
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                            $extension = $file->getClientOriginalExtension();
                            $name = 'resume_' . now()->format('Ymd_His_u');

                            return "{$name}/{$name}.{$extension}";
                        })
                ])
                ->action(function (array $data) {
                    $pdfResume = new PdfResumeController();
                    $pdfResume->import($data, Auth::user());
                    Notification::make()
                        ->title('ระบบ Ai กำลังประมวลผล โปรด Refresh หน้าจอเพื่อดูข้อมูล')
                        ->success()
                        ->send();
                    sleep(15);
                    return redirect("/employees");
                })

        ];
    }
}
