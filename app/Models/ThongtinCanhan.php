<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ThongtinCanhan
 * 
 * @property int $maTK
 * @property string $hoTen
 * @property Carbon|null $ngaySinh
 * @property string|null $gioiTinh
 * @property string|null $soDienThoai
 * @property string|null $diaChi
 * 
 * @property Taikhoan $taikhoan
 *
 * @package App\Models
 */
class ThongtinCanhan extends Model
{
	protected $table = 'thongtin_canhan';
	protected $primaryKey = 'maTK';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'maTK' => 'int',
		'ngaySinh' => 'datetime'
	];

	protected $fillable = [
		'maTK',
		'hoTen',
		'ngaySinh',
		'gioiTinh',
		'soDienThoai',
		'diaChi'
	];

	public function taikhoan()
    {
        // Thông tin này thuộc về tài khoản nào (Khóa ngoại: maTK, Khóa chính bảng Taikhoan: maTK)
        return $this->belongsTo(Taikhoan::class, 'maTK', 'maTK');
    }
}
