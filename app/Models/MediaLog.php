<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaLog extends Model
{
    use HasFactory;

    // 1. Chỉ định chính xác tên bảng trong Database (vì Laravel mặc định sẽ tìm bảng số nhiều là media_logs)
    protected $table = 'media_logs';

    // 2. Khai báo các cột được phép chèn dữ liệu nhanh (Mass Assignment)
    protected $guarded = [];
}