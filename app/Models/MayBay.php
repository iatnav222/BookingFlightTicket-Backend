<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MayBay
 * 
 * @property int $maMayBay
 * @property int $maHang
 * @property string $tenMayBay
 * @property string $loai
 * @property int $soGheTong
 * @property string|null $hangSanXuat
 * 
 * @property HangHangKhong $hang_hang_khong
 * @property Collection|ChuyenBay[] $chuyen_bays
 *
 * @package App\Models
 */
class MayBay extends Model
{
	protected $table = 'may_bay';
	protected $primaryKey = 'maMayBay';
	public $timestamps = false;

	protected $casts = [
		'maHang' => 'int',
		'soGheTong' => 'int'
	];

	protected $fillable = [
		'maHang',
		'tenMayBay',
		'loai',
		'soGheTong',
		'hangSanXuat'
	];

	public function hang_hang_khong()
	{
		return $this->belongsTo(HangHangKhong::class, 'maHang');
	}

	public function chuyen_bays()
	{
		return $this->hasMany(ChuyenBay::class, 'maMayBay');
	}
}
