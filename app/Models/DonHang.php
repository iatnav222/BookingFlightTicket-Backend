<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DonHang
 * 
 * @property int $maDonHang
 * @property int|null $maTK
 * @property string $maCodeDonHang
 * @property string|null $maDatChoHang
 * @property Carbon|null $ngayDat
 * @property float $tongTien
 * @property string|null $phuongThucThanhToan
 * @property bool|null $trangThai
 * @property string $thongTinLienHe
 * 
 * @property Taikhoan|null $taikhoan
 * @property Collection|ThanhToan[] $thanh_toans
 * @property Collection|Ve[] $ves
 *
 * @package App\Models
 */
class DonHang extends Model
{
	protected $table = 'don_hang';
	protected $primaryKey = 'maDonHang';
	public $timestamps = false;

	protected $casts = [
		'maTK' => 'int',
		'ngayDat' => 'datetime',
		'tongTien' => 'float',
		'trangThai' => 'bool'
	];

	protected $fillable = [
		'maTK',
		'maCodeDonHang',
		'maDatChoHang',
		'ngayDat',
		'tongTien',
		'phuongThucThanhToan',
		'trangThai',
		'thongTinLienHe'
	];

	public function taikhoan()
    {
        // belongsTo: Model liên kết, khóa ngoại ở bảng DonHang, khóa chính ở bảng Taikhoan
        return $this->belongsTo(Taikhoan::class, 'maTK', 'maTK');
    }

    public function thanh_toans()
    {
        // hasMany: Model liên kết, khóa ngoại ở bảng ThanhToan, khóa chính ở bảng DonHang
        return $this->hasMany(ThanhToan::class, 'maDonHang', 'maDonHang');
    }

    public function ves()
    {
        // hasMany: Model liên kết, khóa ngoại ở bảng Ve, khóa chính ở bảng DonHang
        return $this->hasMany(Ve::class, 'maDonHang', 'maDonHang');
    }
}
