<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Parsers\parseJson;
use function Differ\Parsers\parseYml;

class ParsersTest extends TestCase
{
    /*    public function testGenDiffsExceptions()
        {
            $pathFirst = 'tests/fixtures/befor.json';
            $pathSecond = 'tests/fixtures/after.json';
            $this->expectException(\Exception::class);
            genDiff($pathFirst, $pathSecond);
        }*/

    public function testParseJsonsExceptions()
    {
        $path = 'tests/fixtures/wrong.json';
        $this->expectException(\Exception::class);
        parseJson(file_get_contents($path));
    }
}
