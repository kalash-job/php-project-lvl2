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
        $expectedFromFilesWithTrees = file_get_contents($this->getFixturePath('expected_from_files_with_trees.txt'));

        $expectedPlainFromFilesWithTrees = file_get_contents($this->getFixturePath('expected_plain_with_trees.txt'));

        $expectedInJsonFormatFromFilesWithTrees = file_get_contents($this->getFixturePath('result.json'));

        return [
            [
                $expectedFromFilesWithTrees,
                $this->getFixturePath('before.yml'),
                $this->getFixturePath('after.yml'),
                'pretty'
            ],
            [
                $expectedFromFilesWithTrees,
                $this->getFixturePath('before_with_tree.json', true),
                $this->getFixturePath('after_with_tree.json'),
                'pretty'
            ],
            [
                $expectedPlainFromFilesWithTrees,
                $this->getFixturePath('before_with_tree.json'),
                $this->getFixturePath('after_with_tree.json'),
                'plain'
            ],
            [
                $expectedInJsonFormatFromFilesWithTrees,
                $this->getFixturePath('before_with_tree.json'),
                $this->getFixturePath('after_with_tree.json'),
                'json'
            ]
        ];
    }

    public function testGenDiffsExceptions()
    {
        $pathFirst = $this->getFixturePath('before_with_tre.json');
        $pathSecond = $this->getFixturePath('after_with_tree.json');
        $this->expectException(\Exception::class);
        genDiff($pathFirst, $pathSecond, 'pretty');
    }
}
