<?php

namespace App\Traits;

/**
 * @method static string getResource()
 */
trait RedirectsToPageList
{
    /**
     * Tự động điều hướng về trang danh sách sau khi Tạo hoặc Sửa thành công
     */
    protected function getRedirectUrl(): string
    {
        // Gọi dạng static đúng chuẩn của Filament: $this->getResource()
        return $this->getResource()::getUrl('index');
    }
}
