<?php

namespace App\DichVu;

use Illuminate\Support\Facades\Http;

class DichVuDuffel
{
    private function layToken(): string
    {
        return (string) config('services.duffel.access_token');
    }

    public function timChuyenBay(array $thamSo)
    {
        $token = $this->layToken();

        if (empty($token) || $token === 'duffel_test_...') {
            throw new \Exception('Chua cau hinh DUFFEL_ACCESS_TOKEN hop le. Vui long them token that vao file .env');
        }

        // Tao payload cho Duffel (Offer Requests)
        $payload = [
            'data' => [
                'slices' => [
                    [
                        'origin' => $thamSo['origin'],             // VD: "HAN" hoac "LHR"
                        'destination' => $thamSo['destination'],   // VD: "SGN" hoac "JFK"
                        'departure_date' => $thamSo['departureDate'],
                    ]
                ],
                'passengers' => [],
                'cabin_class' => 'economy', // phothong
                'return_offers' => true,
            ]
        ];

        // Them so luong hanh khach (nguoi lon) vao payload
        $soNguoiLon = (int) ($thamSo['adults'] ?? 1);
        for ($i = 0; $i < $soNguoiLon; $i++) {
            $payload['data']['passengers'][] = ['type' => 'adult'];
        }

        // Goi API Duffel
        $res = Http::withToken($token)
            ->withHeaders([
                'Duffel-Version' => config('services.duffel.api_version', 'v2'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.duffel.com/air/offer_requests', $payload);

        if (!$res->successful()) {
            throw new \Exception('Loi khi goi Duffel API: ' . $res->body());
        }

        $data = $res->json('data');
        $offers = $data['offers'] ?? [];

        // Parse danh sach offer tra ve cho FE dang gon gheng
        $ketQua = [];
        foreach ($offers as $offer) {
            $slice = $offer['slices'][0] ?? null;
            if (!$slice) continue;

            // Lay segment cuoi cung de lay thoi gian den
            $segments = $slice['segments'] ?? [];
            $lastSegment = !empty($segments) ? $segments[count($segments) - 1] : null;

            $ketQua[] = [
                'id' => $offer['id'],
                'gia' => $offer['total_amount'],
                'tien_te' => $offer['total_currency'],
                'hang_xac_nhan' => $offer['owner']['name'] ?? null,
                'logo_hang' => $offer['owner']['logo_symbol_url'] ?? null,
                'di' => [
                    'ma_san_bay' => $slice['origin']['iata_code'] ?? null,
                    'thoi_gian' => $segments[0]['departing_at'] ?? null,
                ],
                'den' => [
                    'ma_san_bay' => $slice['destination']['iata_code'] ?? null,
                    'thoi_gian' => $lastSegment['arriving_at'] ?? null,
                ],
                'chi_tiet_chuyen' => $segments,
                'raw' => $offer // Luu nguyen ban cho viec dat ve/kiem tra sau nay
            ];
        }

        return [
            'danh_sach' => $ketQua,
            'raw_offer_request_id' => $data['id'] ?? null,
        ];
    }
}
