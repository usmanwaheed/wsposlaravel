<?php

namespace Tests\Unit;

use App\Support\Barcode\Code128Generator;
use PHPUnit\Framework\TestCase;

class Code128GeneratorTest extends TestCase
{
    public function test_generates_svg_markup_for_valid_code(): void
    {
        $generator = new Code128Generator();

        $svg = $generator->generate('SKU-ABC-001');

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringContainsString('SKU-ABC-001', $svg);
        $this->assertStringContainsString('<rect', $svg);
    }

    public function test_rejects_values_outside_code_set_b(): void
    {
        $generator = new Code128Generator();

        $this->expectException(\InvalidArgumentException::class);
        $generator->generate("SKU\x07");
    }
}
