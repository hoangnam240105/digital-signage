<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * LẤY DỮ LIỆU TỪ DB ĐỔ RA FORM
     * Chuyển chuỗi file_path thành dữ liệu phù hợp cho ô Upload hoặc ô Nhập link
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['file_type'] ?? '') === 'url') {
            $data['input_type'] = 'link';
            $data['file_link'] = $data['file_path'] ?? null;
        } else {
            $data['input_type'] = 'upload';
            // FileUpload của Filament phải nhận giá trị dạng mảng []
            $data['file_upload'] = $data['file_path'] ? [$data['file_path']] : [];
        }

        return $data;
    }

    /**
     * XỬ LÝ DỮ LIỆU TRÊN FORM TRƯỚC KHI LƯU XUỐNG DB
     * Gộp dữ liệu từ ô Upload hoặc ô Nhập link lại thành một chuỗi duy nhất để lưu vào cột file_path
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['file_upload'] ?? null) {
            $data['file_path'] = is_array($data['file_upload']) ? array_values($data['file_upload'])[0] : $data['file_upload'];
        } elseif ($data['file_link'] ?? null) {
            $data['file_path'] = $data['file_link'];
        }

        // Xóa các trường ảo (file_upload, file_link) trước khi lưu để DB không báo lỗi thừa cột
        unset($data['file_upload'], $data['file_link']);

        return $data;
    }
}