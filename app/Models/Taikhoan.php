<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
class Taikhoan extends Model
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
		return $this->hasMany(DonHang::class, 'maTK');
	}

	public function hanh_khaches()
	{
		return $this->hasMany(HanhKhach::class, 'maTK');
	}

	public function lich_su_dang_nhaps()
	{
		return $this->hasMany(LichSuDangNhap::class, 'maTK');
	}

	public function thongtin_canhan()
	{
		return $this->hasOne(ThongtinCanhan::class, 'maTK');
	}

	public function ves()
	{
		return $this->hasMany(Ve::class, 'maTK');
	}
}
