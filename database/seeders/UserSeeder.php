<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Đảm bảo dòng này đã có

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ trong bảng để tránh bị trùng lặp khi chạy lệnh nhiều lần
        DB::table('users')->delete();

        // Thêm dữ liệu mẫu
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Tran Van Tai'],
            ['id' => 2, 'name' => 'Nguyen Van A'],
            ['id' => 3, 'name' => 'Le Thi B'],
            ['id' => 4, 'name' => 'Hoang Van C'],
        ]);
    }
}