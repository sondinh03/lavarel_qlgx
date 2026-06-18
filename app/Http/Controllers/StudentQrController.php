<?php

namespace App\Http\Controllers;

use App\Models\StudentNew;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentQrController extends Controller
{
    /**
     * Trả ảnh QR cho qr_token học sinh (SVG, không phụ thuộc dịch vụ ngoài).
     */
    public function show(string $token): Response
    {
        abort_unless(
            preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $token),
            404
        );

        abort_unless(StudentNew::where('qr_token', $token)->exists(), 404);

        $size = min(max((int) request('size', 200), 64), 512);

        $svg = QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->color(20, 82, 36)
            ->backgroundColor(255, 255, 255)
            ->errorCorrection('M')
            ->generate($token);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
