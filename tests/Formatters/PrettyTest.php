<?php

namespace Differ\Tests\Formatters;

use PHPUnit\Framework\TestCase;

use function Differ\Pretty\renderDiff;

class PrettyTest extends TestCase
{
    public function testRenderDiff()
    {
        $ast = [
            ['key' => 'host', 'value' => 'hexlet.io', 'type' => 'former'],
            ['key' => 'timeout', 'value' => 20, 'type' => 'renewed'],
            ['key' => 'timeout', 'value' => 50, 'type' => 'removed'],
            ['key' => 'proxy', 'value' => '123.234.53.22', 'type' => 'removed'],
            ['key' => 'verbose', 'value' => 'true', 'type' => 'added']
        ];
        $expected = implode("\n", [
            "{",
            '    host: hexlet.io',
            '  + timeout: 20',
            '  - timeout: 50',
            '  - proxy: 123.234.53.22',
            '  + verbose: true',
            "}\n"]);
        $actual = renderDiff($ast);
        $this->assertEquals($expected, $actual);
    }
}
