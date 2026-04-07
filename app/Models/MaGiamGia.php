<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MaGiamGia
 * 
 * @property int $maGiamGia
 * @property float $giamPhanTram
 * @property int $soLuongToiDa
 * @property Carbon $ngayBatDau
 * @property Carbon $ngayKetThuc
 * @property bool|null $trangThai
 * @property string $ten_km
 * @property string $type
 * @property string|null $anh
 * @property string|null $dieukien
 * 
 * @property Collection|Ve[] $ves
 *
 * @package App\Models
 */
class MaGiamGia extends Model
{
	protected $table = 'ma_giam_gia';
	protected $primaryKey = 'maGiamGia';
	public $timestamps = false;

	protected $casts = [
		'giamPhanTram' => 'float',
		'soLuongToiDa' => 'int',
		'ngayBatDau' => 'datetime',
		'ngayKetThuc' => 'datetime',
		'trangThai' => 'bool'
	];

	protected $fillable = [
		'giamPhanTram',
		'soLuongToiDa',
		'ngayBatDau',
		'ngayKetThuc',
		'trangThai',
		'ten_km',
		'type',
		'anh',
		'dieukien'
	];

	public function ves()
    {
        return $this->hasMany(Ve::class, 'maGiamGia', 'maGiamGia');
    }
}
