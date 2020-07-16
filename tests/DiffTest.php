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

    private function getFixturePath($filename, $absolutePath = false)
    {
        $fixtureDirPath = $absolutePath ? __DIR__ . '/fixtures/' : 'tests/fixtures/';
        return "{$fixtureDirPath}{$filename}";
    }

    public function pathsProvider()
    {
        $expected = file_get_contents($this->getFixturePath('expected.txt'));

        $expectedAfterEmptyJson = file_get_contents($this->getFixturePath('expected_after_empty_json.txt'));

        $expectedInPlainFormat = file_get_contents($this->getFixturePath('expected_in_plain_format.txt'));

        $expectedFromFilesWithTrees = file_get_contents($this->getFixturePath('expected_from_files_with_trees.txt'));

        $expectedPlainFromFilesWithTrees = file_get_contents($this->getFixturePath('expected_plain_with_trees.txt'));

        $expectedInJsonFormat = file_get_contents($this->getFixturePath('expected_in_json_format.json'));

        $expectedInJsonFormatFromFilesWithTrees = file_get_contents($this->getFixturePath('result.json'));

        return [
            [
                $expected,
                $this->getFixturePath('before.json', true),
                $this->getFixturePath('after.json', true),
                'pretty'
            ],
            [
                $expected,
                $this->getFixturePath('before.json'),
                $this->getFixturePath('after.json'),
                'pretty'
            ],
            [
                $expected,
                $this->getFixturePath('before.yml'),
                $this->getFixturePath('after.yml'),
                'pretty'
            ],
            [
                $expectedAfterEmptyJson,
                $this->getFixturePath('before.json'),
                $this->getFixturePath('empty.json'),
                'pretty'
            ],
            [
                $expectedFromFilesWithTrees,
                $this->getFixturePath('before_with_tree.json'),
                $this->getFixturePath('after_with_tree.json'),
                'pretty'
            ],
            [
                $expectedInPlainFormat,
                $this->getFixturePath('before.json'),
                $this->getFixturePath('after.json'),
                'plain'
            ],
            [
                $expectedInPlainFormat,
                $this->getFixturePath('before.yml'),
                $this->getFixturePath('after.yml'),
                'plain'
            ],
            [
                $expectedPlainFromFilesWithTrees,
                $this->getFixturePath('before_with_tree.json'),
                $this->getFixturePath('after_with_tree.json'),
                'plain'
            ],
            [
                $expectedInJsonFormat,
                $this->getFixturePath('before.json'),
                $this->getFixturePath('after.json'),
                'json'
            ],
            [
                $expectedInJsonFormatFromFilesWithTrees,
                $this->getFixturePath('before_with_tree.json'),
                $this->getFixturePath('after_with_tree.json'),
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
                $this->getFixturePath('befor.json'),
                $this->getFixturePath('after.json'),
                'pretty'
            ],
            [
                $this->getFixturePath('before.json'),
                $this->getFixturePath('wrong.json'),
                'pretty'
            ],
            [
                $this->getFixturePath('before.yml'),
                $this->getFixturePath('wrong.yml'),
                'pretty'
            ]
        ];
    }
}
