<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin lịch')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên lịch')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Ngày bắt đầu')
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Ngày kết thúc')
                            ->required(),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Giờ bắt đầu')
                            ->required(),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Giờ kết thúc')
                            ->required(),

                        Forms\Components\CheckboxList::make('days_of_week')
                            ->label('Ngày trong tuần')
                            ->options([
                                1 => 'Thứ 2',
                                2 => 'Thứ 3',
                                3 => 'Thứ 4',
                                4 => 'Thứ 5',
                                5 => 'Thứ 6',
                                6 => 'Thứ 7',
                                7 => 'Chủ nhật',
                            ])
                            ->columns(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\Select::make('media')
                            ->multiple()
                            ->relationship('media', 'name')
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên lịch')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Ngày kết thúc')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Giờ bắt đầu')
                    ->time(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Giờ kết thúc')
                    ->time(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
