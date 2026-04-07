<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ThanhToan
 * 
 * @property int $maThanhToan
 * @property int $maDonHang
 * @property string $phuongThuc
 * @property string|null $maGiaoDich
 * @property float $soTien
 * @property string|null $noiDung
 * @property Carbon|null $ngayThanhToan
 * @property string $trangThai
 * 
 * @property DonHang $don_hang
 *
 * @package App\Models
 */
class ThanhToan extends Model
{
	protected $table = 'thanh_toan';
	protected $primaryKey = 'maThanhToan';
	public $timestamps = false;

	protected $casts = [
		'maDonHang' => 'int',
		'soTien' => 'float',
		'ngayThanhToan' => 'datetime'
	];

	protected $fillable = [
		'maDonHang',
		'phuongThuc',
		'maGiaoDich',
		'soTien',
		'noiDung',
		'ngayThanhToan',
		'trangThai'
	];

	public function don_hang()
    {
        // Thanh toán này thuộc về đơn hàng nào (Khóa ngoại: maDonHang, Khóa chính bảng DonHang: maDonHang)
        return $this->belongsTo(DonHang::class, 'maDonHang', 'maDonHang');
    }
}
