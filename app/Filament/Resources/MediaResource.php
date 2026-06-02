<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Filament\Resources\MediaResource\RelationManagers;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin tệp tin')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên hiển thị')
                            ->required(),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Chọn Video/Hình ảnh')
                            ->required()
                            ->disk('public') // Lưu vào thư mục công khai để Mobile tải được
                            ->directory('cms-media') // Tự tạo thư mục riêng cho gọn
                            ->visibility('public')
                            ->preserveFilenames() // Giữ tên file gốc cho dễ quản lý
                            ->maxSize(102400), // Giới hạn 100MB (tùy bạn chỉnh)

                        Forms\Components\Select::make('file_type')
                            ->label('Định dạng')
                            ->options([
                                'image' => 'Hình ảnh',
                                'video' => 'Video',
                                'url' => 'Link Web',
                            ])->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
