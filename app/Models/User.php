<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Chỉ cho phép điền cột name
    protected $fillable = ['name'];

    // Vì bảng không còn cột password và remember_token, 
    // bạn nên xóa hoặc comment lại phần $hidden và casts để tránh lỗi
    protected $hidden = [];

    protected function casts(): array
    {
        return [];
    }
}