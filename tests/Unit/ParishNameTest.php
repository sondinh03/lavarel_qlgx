<?php

namespace Tests\Unit;

use App\Models\ParishNew;
use PHPUnit\Framework\TestCase;

class ParishNameTest extends TestCase
{
    /**
     * @dataProvider parishNames
     */
    public function test_normalizes_parish_name_prefix(string $input, string $expected): void
    {
        $this->assertSame($expected, ParishNew::normalizeName($input));
    }

    public function parishNames(): array
    {
        return [
            ['Bùi Chu', 'Giáo xứ Bùi Chu'],
            ['Giáo xứ Bùi Chu', 'Giáo xứ Bùi Chu'],
            ['giáo   xứ   Bùi Chu', 'Giáo xứ Bùi Chu'],
            ['Giáo xứ Giáo xứ Bùi Chu', 'Giáo xứ Bùi Chu'],
            ['', ''],
        ];
    }
}
