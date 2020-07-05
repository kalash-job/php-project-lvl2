<?php

namespace Differ\Tests\Formatters;

use PHPUnit\Framework\TestCase;

use function Differ\Json\renderJsonDiff;

class JsonTest extends TestCase
{
    public function testRenderJsonDiff()
    {
        $ast = [
            ['key' => 'host', 'value' => 'hexlet.io', 'type' => 'former'],
            ['key' => 'timeout', 'value' => 20, 'type' => 'renewed'],
            ['key' => 'timeout', 'value' => 50, 'type' => 'removed'],
            ['key' => 'proxy', 'value' => '123.234.53.22', 'type' => 'removed'],
            ['key' => 'verbose', 'value' => true, 'type' => 'added']
        ];
        $expected = implode("", [
            "{",
            '"host":"hexlet.io",',
            '"timeout":[',
            '{',
            '"type":"renewed",',
            '"newValue":20,',
            '"oldValue":50',
            '}',
            '],',
            '"proxy":[',
            '{',
            '"type":"removed",',
            '"removingValue":"123.234.53.22"',
            '}',
            '],',
            '"verbose":[',
            '{',
            '"type":"added",',
            '"addingValue":true',
            '}',
            ']',
            "}\n"]);

        $actual = renderJsonDiff($ast);
        $this->assertEquals($expected, $actual);
    }
}
