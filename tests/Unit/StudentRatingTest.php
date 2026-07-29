<?php

namespace Tests\Unit;

use App\Support\StudentRating;
use PHPUnit\Framework\TestCase;

class StudentRatingTest extends TestCase
{
    /**
     * @dataProvider boundaryProvider
     */
    public function test_key_for_respects_boundaries(?float $average, ?string $expected): void
    {
        $this->assertSame($expected, StudentRating::keyFor($average));
    }

    /**
     * @return array<string, array{0: ?float, 1: ?string}>
     */
    public function boundaryProvider(): array
    {
        return [
            'điểm 10 tuyệt đối'   => [10.0, 'XUAT_SAC'],
            'đúng biên 9,5'       => [9.5, 'XUAT_SAC'],
            'sát dưới 9,5'        => [9.49, 'GIOI'],
            'đúng biên 8,0'       => [8.0, 'GIOI'],
            'sát dưới 8,0'        => [7.99, 'KHA'],
            'đúng biên 6,5'       => [6.5, 'KHA'],
            'đúng biên 5,0'       => [5.0, 'TRUNG_BINH'],
            'đúng biên 3,5'       => [3.5, 'YEU'],
            'sát dưới 3,5'        => [3.49, 'KEM'],
            'điểm 0'              => [0.0, 'KEM'],
            'điểm âm không xếp'   => [-1.0, null],
            'chưa có điểm'        => [null, null],
        ];
    }

    public function test_label_and_badge_fall_back_when_missing_average(): void
    {
        $this->assertSame('', StudentRating::labelFor(null));
        $this->assertSame('—', StudentRating::labelFor(null, '—'));
        $this->assertSame(StudentRating::BADGE_CLASS_NONE, StudentRating::badgeClassFor(null));
        $this->assertNull(StudentRating::hexFor(null));
    }

    public function test_label_and_badge_follow_the_level(): void
    {
        $this->assertSame('Xuất sắc', StudentRating::labelFor(9.8));
        $this->assertSame('Trung bình', StudentRating::labelFor(5.2));
        $this->assertSame('bg-blue-50/80 text-blue-700', StudentRating::badgeClassFor(8.4));
        $this->assertSame('#ef4444', StudentRating::hexFor(1.0));
    }

    public function test_label_for_key_handles_unknown_key(): void
    {
        $this->assertSame('Khá', StudentRating::labelForKey('KHA'));
        $this->assertSame('', StudentRating::labelForKey('KHONG_CO'));
        $this->assertSame('—', StudentRating::labelForKey(null, '—'));
    }

    public function test_scale_describes_every_level_in_order(): void
    {
        $scale = StudentRating::scale();

        $this->assertCount(6, $scale);
        $this->assertSame(StudentRating::keys(), array_column($scale, 'key'));
        $this->assertSame('Từ 9,5 đến 10', $scale[0]['range']);
        $this->assertSame('Từ 6,5 đến dưới 8,0', $scale[2]['range']);
        $this->assertSame('Dưới 3,5', $scale[5]['range']);
    }

    public function test_legend_uses_compact_ranges(): void
    {
        $legend = array_column(StudentRating::legend(), null, 'key');

        $this->assertSame('9,5–10', $legend['XUAT_SAC']['range']);
        $this->assertSame('6,5–8,0', $legend['KHA']['range']);
        $this->assertSame('0–3,5', $legend['KEM']['range']);
        $this->assertSame('bg-emerald-500', $legend['XUAT_SAC']['dot']);
    }
}
