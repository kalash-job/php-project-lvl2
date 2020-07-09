<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Diff\genDiff;

class DiffTest extends TestCase
{
    /**
     * @dataProvider pathsProvider
     */
    public function testGenDiffWithPaths($expected, $pathFirst, $pathSecond, $format)
    {
        $actual = genDiff($pathFirst, $pathSecond, $format);
        $this->assertEquals($expected, $actual);
    }

    public function pathsProvider()
    {
        $expected = implode("\n", [
            "{",
            '    host: hexlet.io',
            '  + timeout: 20',
            '  - timeout: 50',
            '  - proxy: 123.234.53.22',
            '  + verbose: true',
            "}\n"]);

        $expectedAfterEmptyJson = implode("\n", [
            "{",
            '  - host: hexlet.io',
            '  - timeout: 50',
            '  - proxy: 123.234.53.22',
            "}\n"]);

        $expectedInPlainFormat = implode("\n", [
            "Property 'timeout' was changed. From 50 to 20",
            "Property 'proxy' was removed",
            "Property 'verbose' was added with value: true\n"
        ]);

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

        $expectedPlainFromFilesWithTreesLines = [
            "Property 'common.setting2' was removed",
            "Property 'common.setting6' was removed",
            "Property 'common.setting4' was added with value: 'blah blah'",
            "Property 'common.setting5' was added with value: 'complex value'",
            "Property 'group1.baz' was changed. From 'bas' to 'bars'",
            "Property 'group2' was removed",
            "Property 'group3' was added with value: 'complex value'\n",
        ];
        $expectedInPlainFormatFromFilesWithTrees = implode("\n", $expectedPlainFromFilesWithTreesLines);

        $expectedInJsonFormat = implode("", [
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

        $json = file_get_contents('tests/fixtures/result.json');
        $expectedInJsonFormatFromFilesWithTrees = "{$json}\n";

        return [
            [
                $expected,
                __DIR__ . '/fixtures/before.json',
                __DIR__ . '/fixtures/after.json',
                'pretty'
            ],
            [
                $expected,
                'tests/fixtures/before.json',
                'tests/fixtures/after.json',
                'pretty'
            ],
            [
                $expected,
                'tests/fixtures/before.yml',
                'tests/fixtures/after.yml',
                'pretty'
            ],
            [
                $expectedAfterEmptyJson,
                'tests/fixtures/before.json',
                'tests/fixtures/empty.json',
                'pretty'
            ],
            [
                $expectedFromFilesWithTrees,
                'tests/fixtures/before_with_tree.json',
                'tests/fixtures/after_with_tree.json',
                'pretty'
            ],
            [
                $expectedInPlainFormat,
                'tests/fixtures/before.json',
                'tests/fixtures/after.json',
                'plain'
            ],
            [
                $expectedInPlainFormat,
                'tests/fixtures/before.yml',
                'tests/fixtures/after.yml',
                'plain'
            ],
            [
                $expectedInPlainFormatFromFilesWithTrees,
                'tests/fixtures/before_with_tree.json',
                'tests/fixtures/after_with_tree.json',
                'plain'
            ],
            [
                $expectedInJsonFormat,
                'tests/fixtures/before.json',
                'tests/fixtures/after.json',
                'json'
            ],
            [
                $expectedInJsonFormatFromFilesWithTrees,
                'tests/fixtures/before_with_tree.json',
                'tests/fixtures/after_with_tree.json',
                'json'
            ]
        ];
    }

    /**
     * @dataProvider pathsForExceptionsProvider
     */
    public function testGenDiffsExceptions($pathFirst, $pathSecond, $format)
    {
        $this->expectException(\Exception::class);
        genDiff($pathFirst, $pathSecond, 'pretty');
    }

    public function pathsForExceptionsProvider()
    {
        return [
            [
                'tests/fixtures/befor.json',
                'tests/fixtures/after.json',
                'pretty'
            ],
            [
                'tests/fixtures/before.json',
                'tests/fixtures/wrong.json',
                'pretty'
            ],
            [
                'tests/fixtures/before.yml',
                'tests/fixtures/wrong.yml',
                'pretty'
            ]
        ];
    }
}
