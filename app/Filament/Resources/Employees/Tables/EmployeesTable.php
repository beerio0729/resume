<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Employee;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\Alignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Split::make([
                    // // TextColumn::make('#')
                    // //     ->alignment(Alignment::End)
                    // //     ->rowIndex()
                    // //     ->grow(false)
                    // //     ->visibleFrom('sm')
                    //     ->extraAttributes(['style' => 'width:20px']),
                    ImageColumn::make('image')
                        ->disk('s3_public')
                        ->visibility('private')
                        ->circular()
                        ->grow(false),
                    TextColumn::make('name')->label('ชื่อ')->searchable()->sortable()
                        ->prefix(fn(Employee $a): string => $a->prefix_name . ' ')
                        ->suffix(fn(Employee $a): string => ' ' . $a->last_name),
                    Stack::make([
                        TextColumn::make('email')->icon('heroicon-m-envelope')->iconColor('warning')->copyable()
                            ->copyMessage('คัดลอกแล้ว')->copyMessageDuration(1500)->searchable()->sortable(),
                        TextColumn::make('tel')->icon('heroicon-m-phone')->iconColor('primary'),
                    ])->space(1),
                    Stack::make([
                        TextColumn::make('date_of_birth')
                            ->buddhistDate('d M Y')
                            ->icon('heroicon-m-cake')
                            ->iconColor(Color::hex('#f05ff0')),
                        TextColumn::make('age')
                            ->icon('heroicon-m-identification')
                            ->iconColor(Color::hex('#0ff'))
                            ->prefix('อายุ : ')
                            ->suffix(' ปี'),
                    ])->space(1),

                    //->defaultImageUrl(url('storage/user.png')),
                ])->From('sm'),

                Panel::make([
                    Grid::make(3)
                        ->schema([
                            Stack::make([
                                TextColumn::make('empHasonelocation.address')->label('ทีอยู่')->searchable()->sortable()
                                    ->prefix('ที่อยู่: '),
                                TextColumn::make('empHasonelocation.empBelongtosubdistrict.name_th')->label('ตำบล')->searchable()->sortable()
                                    ->prefix('แขวง/ตำบล : '),
                                TextColumn::make('empHasonelocation.empBelongtodistrict.name_th')->label('อำเภอ')->searchable()->sortable()
                                    ->prefix('เขต/อำเภอ : '),
                                TextColumn::make('empHasonelocation.empBelongtoprovince.name_th')->label('จังหวัด')->searchable()->sortable()
                                    ->prefix('จังหวัด : '),
                                TextColumn::make('empHasonelocation.zipcode')->label('รหัสไปรษณีย์')->searchable()->sortable()
                                    ->prefix('รหัสไปรษณีย์ : '),
                            ])->space(1)->columnSpan(1),
                            Stack::make([
                                TextColumn::make('work_experience_summary')->html(),
                            ])->space(1)->columnSpan(2),
                        ])
                ])->collapsed(true)
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
