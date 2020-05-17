<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Diff\getDiff;

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
}
