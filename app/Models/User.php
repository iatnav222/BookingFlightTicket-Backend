<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // QUAN TRỌNG: Thêm dòng này để báo Laravel đừng tìm 2 cột ngày tháng nữa
    public $timestamps = false;

    protected $fillable = ['name'];

    protected $hidden = [];

    protected function casts(): array
    {
        return [];
    }
}