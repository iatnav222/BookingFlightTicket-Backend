<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LichSuDangNhap
 * 
 * @property int $maLS
 * @property int $maTK
 * @property Carbon|null $ngayDangNhap
 * @property string|null $ip
 * @property bool|null $thanhCong
 * 
 * @property Taikhoan $taikhoan
 *
 * @package App\Models
 */
class LichSuDangNhap extends Model
{
	protected $table = 'lich_su_dang_nhap';
	protected $primaryKey = 'maLS';
	public $timestamps = false;

	protected $casts = [
		'maTK' => 'int',
		'ngayDangNhap' => 'datetime',
		'thanhCong' => 'bool'
	];

	protected $fillable = [
		'maTK',
		'ngayDangNhap',
		'ip',
		'thanhCong'
	];

	public function taikhoan()
	{
		return $this->belongsTo(Taikhoan::class, 'maTK');
	}
}
