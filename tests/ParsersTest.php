<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Parsers\parseJson;
use function Differ\Parsers\parseYaml;

class ParsersTest extends TestCase
{
    public function testParseJsonsExceptions()
    {
        $path = 'tests/fixtures/wrong.json';
        $this->expectException(\Exception::class);
        parseJson(file_get_contents($path));
    }

    public function testParseYamlsExceptions()
    {
        $path = 'tests/fixtures/wrong.yml';
        $this->expectException(\Exception::class);
        parseYaml(file_get_contents($path));
    }
}
