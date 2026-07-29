<?php

namespace Tests\Unit;

use App\Models\Deanery;
use PHPUnit\Framework\TestCase;

class DeaneryNameTest extends TestCase
{
    /**
     * @dataProvider deaneryNames
     */
    public function test_normalizes_deanery_name_prefix(string $input, string $expected): void
    {
        $this->assertSame($expected, Deanery::normalizeName($input));
    }

    public function deaneryNames(): array
    {
        return [
            ['Bùi Chu', 'Giáo hạt Bùi Chu'],
            ['Giáo hạt Bùi Chu', 'Giáo hạt Bùi Chu'],
            ['giáo   hạt   Bùi Chu', 'Giáo hạt Bùi Chu'],
            ['Giáo hạt Giáo hạt Bùi Chu', 'Giáo hạt Bùi Chu'],
            ['', ''],
        ];
    }
}
