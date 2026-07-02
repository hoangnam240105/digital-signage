<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả'),
            
            'image' => Tab::make('Hình ảnh')
                ->icon('heroicon-m-photo')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('file_type', 'image')),
                
            'video' => Tab::make('Video')
                ->icon('heroicon-m-video-camera')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('file_type', 'video')),
                
            'url' => Tab::make('Link Web')
                ->icon('heroicon-m-link')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('file_type', 'url')),
            'audio'=> Tab::make('Âm thanh')
                ->icon('heroicon-m-musical-note')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('file_type', 'audio')),
        ];
    }
}