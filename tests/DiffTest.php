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
        $expected = file_get_contents('tests/fixtures/expected.txt');

        $expectedAfterEmptyJson = file_get_contents('tests/fixtures/expected_after_empty_json.txt');

        $expectedInPlainFormat = file_get_contents('tests/fixtures/expected_in_plain_format.txt');

        $expectedFromFilesWithTrees = file_get_contents('tests/fixtures/expected_from_files_with_trees.txt');

        $expectedPlainFormatFilesWithTrees = file_get_contents('tests/fixtures/expected_plain_files_with_trees.txt');

        $expectedInJsonFormat = file_get_contents('tests/fixtures/expected_in_json_format.json');

        $expectedInJsonFormatFromFilesWithTrees = file_get_contents('tests/fixtures/result.json');

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
                $expectedPlainFormatFilesWithTrees,
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
