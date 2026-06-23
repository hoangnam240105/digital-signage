<?php

namespace App\Filament\Resources\AddressResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DevicesRelationManager extends RelationManager
{
    protected static string $relationship = 'devices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên màn hình / Thiết bị')
                    ->searchable(),

                Tables\Columns\TextColumn::make('device_code')
                    ->label('Mã định danh Box'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Nút bấm giúp hiện tất cả thiết bị để CHỌN
                Tables\Actions\AssociateAction::make()
                    ->label('Gán thiết bị vào địa điểm này')
                    ->modalHeading('Chọn thiết bị từ danh sách tổng')
                    ->preloadRecordSelect() // Hiện danh sách thả xuống chọn cực nhanh
            ])
            ->actions([
                // Nút gỡ thiết bị ra khỏi địa chỉ này (Khi gỡ nó sẽ set address_id = null)
                Tables\Actions\DissociateAction::make()
                    ->label('Bỏ chọn')
                    ->modalHeading('Bạn có chắc muốn gỡ thiết bị này?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Cho phép tích chọn gỡ nhiều thiết bị cùng lúc
                    Tables\Actions\DissociateBulkAction::make()->label('Bỏ chọn các mục đã tích'),
                ]),
            ]);
    }
}