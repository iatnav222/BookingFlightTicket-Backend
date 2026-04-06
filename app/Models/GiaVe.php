<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GiaVe
 * 
 * @property int $maGiaVe
 * @property int $maChuyenBay
 * @property string $loaiHanhKhach
 * @property string $loaiGhe
 * @property float $giaTien
 * @property string|null $ghiChu
 * 
 * @property ChuyenBay $chuyen_bay
 * @property Collection|Ve[] $ves
 *
 * @package App\Models
 */
class GiaVe extends Model
{
	protected $table = 'gia_ve';
	protected $primaryKey = 'maGiaVe';
	public $timestamps = false;

	protected $casts = [
		'maChuyenBay' => 'int',
		'giaTien' => 'float'
	];

	protected $fillable = [
		'maChuyenBay',
		'loaiHanhKhach',
		'loaiGhe',
		'giaTien',
		'ghiChu'
	];

	public function chuyen_bay()
	{
		return $this->belongsTo(ChuyenBay::class, 'maChuyenBay');
	}

	public function ves()
	{
		return $this->hasMany(Ve::class, 'maGiaVe');
	}
}
