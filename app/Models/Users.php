<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model {

    protected $table = 'users';

    protected $primaryKey = 'id';

    // Các thuộc tính được cập nhật vào cở sở dữ liệu
    protected $fillable = [
        'messengerID',
        'studentCode',
        'password',
        'studentName',
        'status',
        'updateTime',
    ];

    // Các thuộc tính được bảo vệ, không cập nhật vào cơ sở dữ liệu
    protected $guarded = [
    ];

    // Các thuộc tính bị ẩn khi lấy dữ liệu
    protected $hidden = [
        'id',
        'password',
        'created_at',
        'updated_at'
    ];
}
