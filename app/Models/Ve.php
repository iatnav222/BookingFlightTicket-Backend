<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ve
 * 
 * @property int $maVe
 * @property int $maDonHang
 * @property int $maChuyenBay
 * @property int $maHanhKhach
 * @property int $maGiaVe
 * @property float $giaMuaThucTe
 * @property string $trangThaiVe
 * @property int|null $maGiamGia
 * @property int|null $maTK
 * @property string $maGhe
 * 
 * @property DonHang $don_hang
 * @property ChuyenBay $chuyen_bay
 * @property GiaVe $gia_ve
 * @property HanhKhach $hanh_khach
 * @property MaGiamGium|null $ma_giam_gium
 * @property Taikhoan|null $taikhoan
 *
 * @package App\Models
 */
class Ve extends Model
{
	protected $table = 've';
	protected $primaryKey = 'maVe';
	public $timestamps = false;

	protected $casts = [
		'maDonHang' => 'int',
		'maChuyenBay' => 'int',
		'maHanhKhach' => 'int',
		'maGiaVe' => 'int',
		'giaMuaThucTe' => 'float',
		'maGiamGia' => 'int',
		'maTK' => 'int'
	];

	protected $fillable = [
		'maDonHang',
		'maChuyenBay',
		'maHanhKhach',
		'maGiaVe',
		'giaMuaThucTe',
		'trangThaiVe',
		'maGiamGia',
		'maTK',
		'maGhe'
	];

	public function don_hang()
	{
		return $this->belongsTo(DonHang::class, 'maDonHang');
	}

	public function chuyen_bay()
	{
		return $this->belongsTo(ChuyenBay::class, 'maChuyenBay');
	}

	public function gia_ve()
	{
		return $this->belongsTo(GiaVe::class, 'maGiaVe');
	}

	public function hanh_khach()
	{
		return $this->belongsTo(HanhKhach::class, 'maHanhKhach');
	}

	public function ma_giam_gium()
	{
		return $this->belongsTo(MaGiamGium::class, 'maGiamGia');
	}

	public function taikhoan()
	{
		return $this->belongsTo(Taikhoan::class, 'maTK');
	}
}
