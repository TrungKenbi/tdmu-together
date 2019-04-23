<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lichthi extends Model
{

    protected $table = 'lichthi';

    protected $primaryKey = 'id';

    // Các thuộc tính được cập nhật vào cở sở dữ liệu
    protected $fillable = [
        'user',
        'MaMH',
        'TenMH',
        'Nhom',
        'To',
        'SiSo',
        'NgayThi',
        'TGThi',
        'SoPhut',
        'PhongThi',
        'HinhThuc',
    ];

    // Các thuộc tính được bảo vệ, không cập nhật vào cơ sở dữ liệu
    protected $guarded = [

    ];

    // Các thuộc tính bị ẩn khi lấy dữ liệu
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];
}
