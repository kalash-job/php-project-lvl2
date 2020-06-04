<?php

namespace Differ\Diff;

use function Differ\Parsers\parseJson;
use function Differ\Parsers\parseYaml;

function getDiff(array $before, array $after): array
{
    $keys = array_keys(array_merge($before, $after));
    return array_reduce($keys, function ($acc, $key) use ($before, $after) {
        $prefixMinus = '- ';
        $prefixPlus = '+ ';
        $prefixSpace = '  ';
        $correctValue = function ($value) {
            if ($value === true) {
                return 'true';
            } elseif ($value === false) {
                return 'false';
            } else {
                return $value;
            }
        };
        if (isset($before[$key]) && isset($after[$key])) {
            if ($before[$key] === $after[$key]) {
                $acc[] = "$prefixSpace{$key}: {$before[$key]}";
                return $acc;
            }
            $acc[] = "{$prefixPlus}{$key}: {$correctValue($after[$key])}";
            $acc[] = "{$prefixMinus}{$key}: {$correctValue($before[$key])}";
            return $acc;
        } elseif (isset($before[$key])) {
            $acc[] = "{$prefixMinus}{$key}: {$correctValue($before[$key])}";
            return $acc;
        }
        $acc[] = "{$prefixPlus}{$key}: {$correctValue($after[$key])}";
        return $acc;
    }, []);
}

function genDiff(string $pathFirst, string $pathSecond, $format = null)
{
    try {
        if (!file_exists($pathFirst)) {
            throw new \Exception("File {$pathFirst} not found. You should write a correct path to the file\n");
        } elseif (!file_exists($pathSecond)) {
            throw new \Exception("File {$pathSecond} not found. You should write a correct path to the file\n");
        }
        $extensionFirst = pathinfo($pathFirst, PATHINFO_EXTENSION);
        $extensionSecond = pathinfo($pathSecond, PATHINFO_EXTENSION);
        if ($extensionFirst !== 'json' && $extensionFirst !== 'yml') {
            throw new \Exception("File {$pathFirst} must be in JSON or YAML format\n");
        } elseif ($extensionSecond !== 'json' && $extensionSecond !== 'yml') {
            throw new \Exception("File {$pathSecond} must be in JSON or YAML format\n");
        }
        if ($extensionFirst === 'json') {
            $firstData = parseJson(file_get_contents($pathFirst));
        } else {
            $firstData = parseYaml(file_get_contents($pathFirst));
        }
        if ($extensionSecond === 'json') {
            $secondData = parseJson(file_get_contents($pathSecond));
        } else {
            $secondData = parseYaml(file_get_contents($pathSecond));
        }
    } catch (\Exception $e) {
        print_r($e->getMessage());
        die();
    }
    $differences = implode("\n", getDiff($firstData, $secondData));
    return "{$differences}\n";
}
