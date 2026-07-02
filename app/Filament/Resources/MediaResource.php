<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

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

                    Forms\Components\Select::make('input_type')
                        ->label('Hình thức nội dung')
                        ->options([
                            'upload' => 'Tải tệp lên (Ảnh, Video, Âm thanh)',
                            'link' => 'Nhập liên kết (Link Web URL)',
                        ])
                        ->required()
                        ->live()
                        ->dehydrated(false),

                    // 1. Ô Tải file (Sử dụng tên file_upload độc lập)
                    Forms\Components\FileUpload::make('file_upload')
                        ->label('Chọn Tệp tin từ thiết bị')
                        ->disk('public')
                        ->directory('cms-media')
                        ->preserveFilenames()
                        ->visible(fn(Get $get) => $get('input_type') === 'upload')
                        ->required(fn(Get $get) => $get('input_type') === 'upload')
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state) {
                                $file = is_array($state) ? array_values($state)[0] : $state;
                                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                    $set('file_size', $file->getSize());
                                    $mimeType = $file->getMimeType();
                                    if (str_contains($mimeType, 'image')) $set('file_type', 'image');
                                    elseif (str_contains($mimeType, 'video')) $set('file_type', 'video');
                                    elseif (str_contains($mimeType, 'audio')) $set('file_type', 'audio');
                                }
                            }
                        }),

                    // 2. Ô Nhập Link (Sử dụng tên file_link độc lập)
                    Forms\Components\TextInput::make('file_link')
                        ->label('Nhập địa chỉ Trang Web / URL của tệp')
                        ->visible(fn(Get $get) => $get('input_type') === 'link')
                        ->required(fn(Get $get) => $get('input_type') === 'link')
                        ->url()
                        ->placeholder('https://example.com/video.mp4')
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state) {
                                $set('file_type', 'url'); // Tự động đổi loại thành url khi nhập link
                                $set('file_size', null);  // Link web thì không có dung lượng file vật lý
                            }
                        }),

                    Forms\Components\Hidden::make('file_type'),
                    Forms\Components\Hidden::make('file_size'),
                ])
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên hiển thị')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_type')
                    ->label('Định dạng')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'image' => 'success',
                        'video' => 'info',
                        'url'   => 'warning',
                        'audio' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'image' => 'Hình ảnh',
                        'video' => 'Video',
                        'url'   => 'Link Web',
                        'audio' => 'Âm thanh',
                        default => $state ?? 'Chưa rõ',
                    }),

                Tables\Columns\TextColumn::make('file_path')
                    ->label('Đường dẫn / Tệp')
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('Đã copy đường dẫn'),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Dung lượng')
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return '0 KB';
                        }
                        if (!is_numeric($state)) {
                            return $state;
                        }
                        if ($state >= 1048576) {
                            return number_format($state / 1048576, 2) . ' MB';
                        }
                        return number_format($state / 1024, 2) . ' KB';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([])
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
        return [];
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
