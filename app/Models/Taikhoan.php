<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class Taikhoan
 * 
 * @property int $maTK
 * @property string $username
 * @property string $password
 * @property string|null $hoten
 * @property string $email
 * @property string $quyen
 * @property bool|null $trangThai
 * @property Carbon|null $ngayTao
 * 
 * @property Collection|DonHang[] $don_hangs
 * @property Collection|HanhKhach[] $hanh_khaches
 * @property Collection|LichSuDangNhap[] $lich_su_dang_nhaps
 * @property ThongtinCanhan|null $thongtin_canhan
 * @property Collection|Ve[] $ves
 *
 * @package App\Models
 */
class Taikhoan extends Authenticatable
{
	protected $table = 'taikhoan';
	protected $primaryKey = 'maTK';
	public $timestamps = false;

	protected $casts = [
		'trangThai' => 'bool',
		'ngayTao' => 'datetime'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'username',
		'password',
		'hoten',
		'email',
		'quyen',
		'trangThai',
		'ngayTao'
	];

	public function don_hangs()
    {
        return $this->hasMany(DonHang::class, 'maTK', 'maTK');
    }

    public function hanh_khaches()
    {
        return $this->hasMany(HanhKhach::class, 'maTK', 'maTK');
    }

    public function lich_su_dang_nhaps()
    {
        return $this->hasMany(LichSuDangNhap::class, 'maTK', 'maTK');
    }

    public function thongtin_canhan()
    {
        // hasOne cũng tuân thủ quy tắc 3 tham số giống hasMany
        return $this->hasOne(ThongtinCanhan::class, 'maTK', 'maTK');
    }

    public function ves()
    {
        return $this->hasMany(Ve::class, 'maTK', 'maTK');
    }
}
