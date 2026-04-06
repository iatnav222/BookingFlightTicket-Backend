<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SanBay
 * 
 * @property int $maSanBay
 * @property string $maCode
 * @property string $tenSanBay
 * @property string|null $hinhAnh
 * @property string $thanhPho
 * 
 * @property Collection|ChuyenBay[] $chuyen_bays
 *
 * @package App\Models
 */
class SanBay extends Model
{
	protected $table = 'san_bay';
	protected $primaryKey = 'maSanBay';
	public $timestamps = false;

	protected $fillable = [
		'maCode',
		'tenSanBay',
		'hinhAnh',
		'thanhPho'
	];

	public function chuyen_bays()
	{
		return $this->hasMany(ChuyenBay::class, 'maSanBayDi');
	}
}
