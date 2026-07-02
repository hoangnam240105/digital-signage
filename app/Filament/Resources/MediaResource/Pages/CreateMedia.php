<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\RedirectsToPageList;

class CreateMedia extends CreateRecord
{
    use RedirectsToPageList;
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    if ($data['file_upload'] ?? null) {
        // Lấy file đầu tiên từ mảng upload
        $data['file_path'] = is_array($data['file_upload']) ? array_values($data['file_upload'])[0] : $data['file_upload'];
    } elseif ($data['file_link'] ?? null) {
        $data['file_path'] = $data['file_link'];
    }

    // Xóa bỏ các trường dữ liệu thừa không có trong bảng DB
    unset($data['file_upload'], $data['file_link']);

    return $data;
}
}
