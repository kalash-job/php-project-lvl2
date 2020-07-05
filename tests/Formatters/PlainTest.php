<?php

namespace Differ\Tests\Formatters;

use PHPUnit\Framework\TestCase;

use function Differ\Plain\renderPlainDiff;

class PlainTest extends TestCase
{
    public function testRenderPlainDiff()
    {
        $ast = [
            ['key' => 'host', 'value' => 'hexlet.io', 'type' => 'former'],
            ['key' => 'timeout', 'value' => 20, 'type' => 'renewed'],
            ['key' => 'timeout', 'value' => 50, 'type' => 'removed'],
            ['key' => 'proxy', 'value' => '123.234.53.22', 'type' => 'removed'],
            ['key' => 'verbose', 'value' => true, 'type' => 'added']
        ];
        $expected = implode("\n", [
            "Property 'timeout' was changed. From 50 to 20",
            "Property 'proxy' was removed",
            "Property 'verbose' was added with value: true\n"
        ]);

        $actual = renderPlainDiff($ast);
        $this->assertEquals($expected, $actual);
    }
}
