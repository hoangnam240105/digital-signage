<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use Filament\Forms;
use App\Filament\Resources\AddressResource\RelationManagers\DevicesRelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class AddressResource extends Resource
{
    protected static ?string $model = 'App\\Models\\Address';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Tên khu vực')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Mô tả địa điểm')
                    ->rows(3)
                    ->columnSpanFull(), // Chiếm hết chiều rộng popup
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->weight('bold')
                        ->size('lg')
                        ->searchable(),

                    Tables\Columns\TextColumn::make('description')
                        ->color('gray')
                        ->limit(50)
                        // Thay thế ->italic() bằng cách thêm class của Tailwind CSS vào đây:
                        ->extraAttributes(['class' => 'italic']),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('md'),
                Tables\Actions\DeleteAction::make(),
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
            DevicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
