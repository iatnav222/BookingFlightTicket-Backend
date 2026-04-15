<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChuyenBay;
use App\Models\GiaVe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChuyenBayController extends Controller
{
    // Danh sach / tim kiem chuyen bay cho client
    // GET /api/client/chuyen-bay?maSanBayDi=&maSanBayDen=&ngayBay=YYYY-MM-DD&maHang=&loaiHanhKhach=&loaiGhe=&maxPrice=&sort=
    public function danhSach(Request $request)
    {
        $loaiHanhKhach = $request->get('loaiHanhKhach', 'NguoiLon');
        $loaiGhe = $request->get('loaiGhe', 'PhoThong');

        $query = ChuyenBay::query()
            ->with(['hang_hang_khong', 'may_bay', 'san_bay_di', 'san_bay_den'])
            ->where('trangThai', 1)
            ->where('soGheConLai', '>', 0);

        // Chi lay chuyen bay tuong lai (mac dinh)
        if (!$request->has('includePast')) {
            $query->where('ngayGioCatCanh', '>=', Carbon::now());
        }

        if ($request->filled('maSanBayDi')) {
            $query->where('maSanBayDi', $request->maSanBayDi);
        }

        if ($request->filled('maSanBayDen')) {
            $query->where('maSanBayDen', $request->maSanBayDen);
        }

        if ($request->filled('ngayBay')) {
            $query->whereDate('ngayGioCatCanh', $request->ngayBay);
        }

        if ($request->filled('maHang')) {
            $query->where('maHang', $request->maHang);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('maChuyenBay', 'like', "%{$search}%")
                    ->orWhereHas('hang_hang_khong', function ($qHang) use ($search) {
                        $qHang->where('tenHang', 'like', "%{$search}%")
                            ->orWhere('maCode', 'like', "%{$search}%");
                    })
                    ->orWhereHas('san_bay_di', function ($qSbDi) use ($search) {
                        $qSbDi->where('tenSanBay', 'like', "%{$search}%")
                            ->orWhere('thanhPho', 'like', "%{$search}%");
                    })
                    ->orWhereHas('san_bay_den', function ($qSbDen) use ($search) {
                        $qSbDen->where('tenSanBay', 'like', "%{$search}%")
                            ->orWhere('thanhPho', 'like', "%{$search}%");
                    });
            });
        }

        // Gia thap nhat theo loai hanh khach + loai ghe
        $minPriceSub = GiaVe::query()
            ->selectRaw('MIN(giaTien)')
            ->whereColumn('maChuyenBay', 'chuyen_bay.maChuyenBay')
            ->where('loaiHanhKhach', $loaiHanhKhach)
            ->where('loaiGhe', $loaiGhe);

        $query->addSelect([
            'gia_thap_nhat' => $minPriceSub,
        ]);

        // Loc theo maxPrice (dua tren bang gia ve)
        if ($request->filled('maxPrice')) {
            $maxPrice = (float) $request->maxPrice;

            $query->whereExists(function ($q) use ($maxPrice, $loaiHanhKhach, $loaiGhe) {
                $q->select(DB::raw(1))
                    ->from('gia_ve')
                    ->whereColumn('gia_ve.maChuyenBay', 'chuyen_bay.maChuyenBay')
                    ->where('gia_ve.loaiHanhKhach', $loaiHanhKhach)
                    ->where('gia_ve.loaiGhe', $loaiGhe)
                    ->where('gia_ve.giaTien', '<=', $maxPrice);
            });
        }

        $sort = $request->get('sort', 'catcanh_asc');
        if ($sort === 'catcanh_desc') {
            $query->orderBy('ngayGioCatCanh', 'desc');
        } elseif ($sort === 'gia_asc') {
            $query->orderBy('gia_thap_nhat', 'asc')->orderBy('ngayGioCatCanh', 'asc');
        } elseif ($sort === 'gia_desc') {
            $query->orderBy('gia_thap_nhat', 'desc')->orderBy('ngayGioCatCanh', 'asc');
        } else {
            $query->orderBy('ngayGioCatCanh', 'asc');
        }

        $perPage = (int) $request->get('perPage', 20);
        $perPage = max(1, min(100, $perPage));

        $danhSach = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lay danh sach chuyen bay thanh cong',
            'data' => $danhSach->items(),
            'pagination' => [
                'current_page' => $danhSach->currentPage(),
                'last_page' => $danhSach->lastPage(),
                'per_page' => $danhSach->perPage(),
                'total' => $danhSach->total(),
            ],
        ], 200);
    }

    // Chi tiet chuyen bay + danh sach gia ve
    public function chiTiet(Request $request, $id)
    {
        $chuyenBay = ChuyenBay::with([
            'hang_hang_khong',
            'may_bay',
            'san_bay_di',
            'san_bay_den',
            'gia_ves',
        ])->find($id);

        if (!$chuyenBay) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay chuyen bay',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lay thong tin chuyen bay thanh cong',
            'data' => $chuyenBay,
        ], 200);
    }
}
