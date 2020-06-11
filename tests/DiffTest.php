<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Diff\getDiff;
use function Differ\Diff\genDiff;

class DiffTest extends TestCase
{
    public function testGetDiff()
    {
        $before = [
            'host' => 'hexlet.io',
            'timeout' => 50,
            'proxy' => '123.234.53.22'
        ];
        $after = [
            'timeout' => 20,
            'verbose' => true,
            'host' => 'hexlet.io'
        ];
        $expected = [
            '  host: hexlet.io',
            '+ timeout: 20',
            '- timeout: 50',
            '- proxy: 123.234.53.22',
            '+ verbose: true'
        ];
        $actual = getDiff($before, $after);
        $this->assertEquals($expected, $actual);
    }

    public function testGetDiffWithEmptyValue()
    {
        $before = [
            'host' => 'hexlet.io',
            'timeout' => 50,
            'proxy' => '123.234.53.22'
        ];
        $after = [];
        $expected = [
            '- host: hexlet.io',
            '- timeout: 50',
            '- proxy: 123.234.53.22'
        ];
        $actual = getDiff($before, $after);
        $this->assertEquals($expected, $actual);
    }

    public function testGetDiffWithBooleanValue()
    {
        $before = [
            'host' => 'hexlet.io',
            'timeout' => 50,
            'proxy' => '123.234.53.22'
        ];
        $after = [
            'timeout' => 0,
            'verbose' => true,
            'host' => 'hexlet.io',
            'result' => false
        ];
        $expected = [
            '  host: hexlet.io',
            '+ timeout: 0',
            '- timeout: 50',
            '- proxy: 123.234.53.22',
            '+ verbose: true',
            '+ result: false'
        ];
        $actual = getDiff($before, $after);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider pathsProvider
     */
    public function testGenDiffWithPaths($expected, $pathFirst, $pathSecond)
    {
        $actual = genDiff($pathFirst, $pathSecond);
        $this->assertEquals($expected, $actual);
    }

    public function pathsProvider()
    {
        $expected = "{\n  host: hexlet.io\n+ timeout: 20\n- timeout: 50\n- proxy: 123.234.53.22\n+ verbose: true\n}\n";
        $expectedFromFilesWithTreesLines = [
            "{",
            "    common: {",
            "        setting1: Value 1",
            "      - setting2: 200",
            "        setting3: true",
            "      - setting6: {",
            "            key: value",
            "        }",
            "      + setting4: blah blah",
            "      + setting5: {",
            "            key5: value5",
            "        }",
            "    }",
            "    group1: {",
            "      + baz: bars",
            "      - baz: bas",
            "        foo: bar",
            "    }",
            "  - group2: {",
            "        abc: 12345",
            "    }",
            "  + group3: {",
            "        fee: 100500",
            "    }",
            "}\n"];
        $expectedFromFilesWithTrees = implode("\n", $expectedFromFilesWithTreesLines);
        return [
            [
                $expected,
                __DIR__ . '/fixtures/before.json',
                __DIR__ . '/fixtures/after.json'
            ],
            [
                $expected,
                'tests/fixtures/before.json',
                'tests/fixtures/after.json'
            ],
            [
                $expected,
                __DIR__ . '/fixtures/before.yml',
                __DIR__ . '/fixtures/after.yml'
            ],
            [
                $expected,
                'tests/fixtures/before.yml',
                'tests/fixtures/after.yml'
            ],
            [
                $expectedFromFilesWithTrees,
                'tests/fixtures/before_with_tree.json',
                'tests/fixtures/after_with_tree.json'
            ]
        ];
    }

    public function testGenDiffsExceptions()
    {
        $pathFirst = 'tests/fixtures/befor.json';
        $pathSecond = 'tests/fixtures/after.json';
        $this->expectException(\Exception::class);
        genDiff($pathFirst, $pathSecond);
    }
}
