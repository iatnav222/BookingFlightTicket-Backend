<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Hiển thị danh sách và trang quản trị
    public function index() {
        $users = User::all();
        return view('users_crud', compact('users'));
    }

    // Thêm user
    public function store(Request $request) {
        User::create($request->only('name'));
        return back();
    }

    // Xóa user
    public function destroy($id) {
        User::destroy($id);
        return back();
    }

    // Cập nhật user
    public function update(Request $request, $id) {
        $user = User::find($id);
        $user->update($request->only('name'));
        return back();
    }
}