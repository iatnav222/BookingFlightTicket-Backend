<?php

namespace App\Http\Controllers;

use App\Models\Taikhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // API Đăng nhập
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 1. Tìm tài khoản theo username
        $user = Taikhoan::where('username', $request->username)->first();

        // 2. Kiểm tra tài khoản tồn tại và mật khẩu có khớp không
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.'
            ], 401);
        }

        // 3. Kiểm tra trạng thái tài khoản (1: Hoạt động, 0: Bị khóa)
        if ($user->trangThai == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa.'
            ], 403);
        }

        // 4. Tạo token (Sử dụng Laravel Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Trả về JSON chứa quyền và token để Frontend chuyển hướng
        return response()->json([
            'success'      => true,
            'message'      => 'Đăng nhập thành công',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'quyen'        => $user->quyen, // Trả về 'admin' hoặc 'user'
            'data'         => $user
        ], 200);
    }
    //chức năng đăng xuất
    public function logout(Request $request)
{
    // Thu hồi (xóa) token mà user đang dùng để gọi request này
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Đăng xuất thành công.'
    ], 200);
}
}