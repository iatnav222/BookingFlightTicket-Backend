<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ChuyenBay
 * 
 * @property int $maChuyenBay
 * @property int $maMayBay
 * @property int $maHang
 * @property int $maSanBayDi
 * @property int $maSanBayDen
 * @property Carbon $ngayGioCatCanh
 * @property Carbon $ngayGioHaCanh
 * @property int $soGheTong
 * @property int $soGheConLai
 * @property bool|null $trangThai
 * 
 * @property HangHangKhong $hang_hang_khong
 * @property MayBay $may_bay
 * @property SanBay $san_bay
 * @property Collection|GiaVe[] $gia_ves
 * @property Collection|Ve[] $ves
 *
 * @package App\Models
 */
class ChuyenBay extends Model
{
	protected $table = 'chuyen_bay';
	protected $primaryKey = 'maChuyenBay';
	public $timestamps = false;

	protected $casts = [
		'maMayBay' => 'int',
		'maHang' => 'int',
		'maSanBayDi' => 'int',
		'maSanBayDen' => 'int',
		'ngayGioCatCanh' => 'datetime',
		'ngayGioHaCanh' => 'datetime',
		'soGheTong' => 'int',
		'soGheConLai' => 'int',
		'trangThai' => 'bool'
	];

	protected $fillable = [
		'maMayBay',
		'maHang',
		'maSanBayDi',
		'maSanBayDen',
		'ngayGioCatCanh',
		'ngayGioHaCanh',
		'soGheTong',
		'soGheConLai',
		'trangThai'
	];

	public function hang_hang_khong()
    {
        return $this->belongsTo(HangHangKhong::class, 'maHang', 'maHang');
    }

    public function may_bay()
    {
        return $this->belongsTo(MayBay::class, 'maMayBay', 'maMayBay');
    }

    // Sân bay cất cánh (Khóa chính của bảng SanBay là maSanBay)
    public function san_bay_di()
    {
        return $this->belongsTo(SanBay::class, 'maSanBayDi', 'maSanBay');
    }

    // Sân bay hạ cánh (Khóa chính của bảng SanBay là maSanBay)
    public function san_bay_den()
    {
        return $this->belongsTo(SanBay::class, 'maSanBayDen', 'maSanBay');
    }

    public function gia_ves()
    {
        return $this->hasMany(GiaVe::class, 'maChuyenBay', 'maChuyenBay');
    }

    public function ves()
    {
        return $this->hasMany(Ve::class, 'maChuyenBay', 'maChuyenBay');
    }
}
