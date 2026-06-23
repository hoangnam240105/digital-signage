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

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

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
                            ->disk('public') 
                            ->directory('cms-media') 
                            ->visibility('public')
                            ->preserveFilenames() 
                            ->maxSize(102400), 

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
            // 1. Cột Tên hiển thị
            Tables\Columns\TextColumn::make('name')
                ->label('Tên hiển thị')
                ->searchable() 
                ->sortable(), 

            // 2. Cột Định dạng (Hiển thị dạng Badge cho đẹp)
            Tables\Columns\TextColumn::make('file_type')
                ->label('Định dạng')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'image' => 'success', 
                    'video' => 'info',    
                    'url'   => 'warning',
                    'music' => 'danger',  
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'image' => 'Hình ảnh',
                    'video' => 'Video',
                    'url'   => 'Link Web',
                    default => $state,
                }),

            // 3. Cột Đường dẫn file / URL
            Tables\Columns\TextColumn::make('file_path')
                ->label('Đường dẫn / Tệp')
                ->limit(40)
                ->copyable()
                ->copyMessage('Đã copy đường dẫn'),

            // 4. Cột Ngày tạo
            Tables\Columns\TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false), // Cho phép ẩn/hiện cột
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
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
