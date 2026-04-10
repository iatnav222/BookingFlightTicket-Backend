<?php

namespace App\Http\Controllers;

use App\Models\Taikhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $taikhoan = Taikhoan::where('username', $validated['username'])->first();

        if (!$taikhoan) {
            return response()->json([
                'success' => false,
                'message' => 'Sai tài khoản hoặc mật khẩu',
            ], 401);
        }

        $inputPassword = $validated['password'];
        $storedPassword = (string) $taikhoan->password;

        $verified = Hash::check($inputPassword, $storedPassword);

        // Tương thích dữ liệu cũ (nếu DB đang lưu plaintext/MD5).
        // Khi xác thực được, tự động nâng cấp sang hash chuẩn của Laravel.
        if (!$verified && hash_equals($storedPassword, $inputPassword)) {
            $verified = true;
        }

        if (!$verified && preg_match('/^[a-f0-9]{32}$/i', $storedPassword) && hash_equals(strtolower($storedPassword), md5($inputPassword))) {
            $verified = true;
        }

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Sai tài khoản hoặc mật khẩu',
            ], 401);
        }

        // Nếu mật khẩu chưa phải dạng hash của Laravel thì cập nhật lại.
        if (!str_starts_with($storedPassword, '$2y$') && !str_starts_with($storedPassword, '$argon2')) {
            $taikhoan->password = Hash::make($inputPassword);
            $taikhoan->save();
        }

        if (($taikhoan->trangThai ?? true) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đang bị khóa',
            ], 403);
        }

        $token = $taikhoan->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'token_type' => 'Bearer',
                'token' => $token,
                'taikhoan' => [
                    'maTK' => $taikhoan->maTK,
                    'username' => $taikhoan->username,
                    'email' => $taikhoan->email,
                    'hoten' => $taikhoan->hoten,
                    'quyen' => $taikhoan->quyen,
                    'trangThai' => $taikhoan->trangThai,
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập',
            ], 401);
        }

        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công',
        ]);
    }
}
