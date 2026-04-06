<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HangHangKhong
 * 
 * @property int $maHang
 * @property string $tenHang
 * @property string $maCode
 * @property string|null $logo
 * @property string|null $ghiChu
 * @property bool|null $trangThai
 * 
 * @property Collection|ChuyenBay[] $chuyen_bays
 * @property Collection|MayBay[] $may_bays
 *
 * @package App\Models
 */
class HangHangKhong extends Model
{
	protected $table = 'hang_hang_khong';
	protected $primaryKey = 'maHang';
	public $timestamps = false;

	protected $casts = [
		'trangThai' => 'bool'
	];

	protected $fillable = [
		'tenHang',
		'maCode',
		'logo',
		'ghiChu',
		'trangThai'
	];

	public function chuyen_bays()
	{
		return $this->hasMany(ChuyenBay::class, 'maHang');
	}

	public function may_bays()
	{
		return $this->hasMany(MayBay::class, 'maHang');
	}
}
