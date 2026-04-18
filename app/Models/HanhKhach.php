<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HanhKhach
 * 
 * @property int $maHanhKhach
 * @property int|null $maTK
 * @property string $hoTen
 * @property Carbon $ngaySinh
 * @property string $gioiTinh
 * @property string $loaiHanhKhach
 * @property string|null $soCMND
 * @property string|null $email
 * @property string|null $sdt
 * 
 * @property Taikhoan|null $taikhoan
 * @property Collection|Ve[] $ves
 *
 * @package App\Models
 */
class HanhKhach extends Model
{
	protected $table = 'hanh_khach';
	protected $primaryKey = 'maHanhKhach';
	public $timestamps = false;

	protected $casts = [
		'maTK' => 'int',
		'ngaySinh' => 'datetime'
	];

	protected $fillable = [
		'maTK',
		'ho',
		'ten',
		'hoTen',
		'ngaySinh',
		'gioiTinh',
		'loaiHanhKhach',
		'soCMND',
		'soHoChieu',
		'quocTich',
		'email',
		'sdt',
		'soDienThoai',
	];

	public function taikhoan()
    {
        // Khách hàng này do tài khoản nào đặt (Khóa ngoại: maTK, Khóa chính bảng Taikhoan: maTK)
        return $this->belongsTo(Taikhoan::class, 'maTK', 'maTK');
    }

    public function ves()
    {
        // Khách hàng này sở hữu những vé nào (Khóa ngoại bảng Ve: maHanhKhach, Khóa chính bảng này: maHanhKhach)
        return $this->hasMany(Ve::class, 'maHanhKhach', 'maHanhKhach');
    }
}
