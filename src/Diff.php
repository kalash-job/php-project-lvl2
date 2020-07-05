<?php

namespace Differ\Diff;

use function Differ\Parsers\parseJson;
use function Differ\Parsers\parseYaml;
use function Differ\Plain\renderPlainDiff;
use function Differ\Pretty\renderDiff;
use function Differ\Json\renderJsonDiff;

function correctValue($value)
{
    if ($value === true) {
        return 'true';
    } elseif ($value === false) {
        return 'false';
    } else {
        return $value;
    }
}

function getDiff($before, $after): array
{
    $iter = function ($nodeBefore, $nodeAfter, $diff) use (&$iter) {
        $firstColl = (array)$nodeBefore;
        $secondColl = (array)$nodeAfter;
        $keys = array_keys(array_merge($firstColl, $secondColl));
        $diff = array_reduce($keys, function ($acc, $key) use ($firstColl, $secondColl, &$iter) {
            $nodeFirst = isset($firstColl[$key]) ? correctValue($firstColl[$key]) : null;
            $nodeSecond = isset($secondColl[$key]) ? correctValue($secondColl[$key]) : null;
            if (!isset($nodeFirst)) {
                $acc[] = ['key' => $key, 'value' => $nodeSecond, 'type' => 'added'];
                return $acc;
            } elseif (!isset($nodeSecond)) {
                $acc[] = ['key' => $key, 'value' => $nodeFirst, 'type' => 'removed'];
                return $acc;
            }
            if (is_object($nodeFirst) && is_object($nodeSecond)) {
                $children = $iter($nodeFirst, $nodeSecond, []);
                $acc[] = ['key' => $key, 'type' => 'former', 'children' => $children];
                return $acc;
            } elseif (is_object($nodeFirst) || is_object($nodeSecond)) {
                $acc[] = ['key' => $key, 'value' => $nodeSecond, 'type' => 'renewed'];
                $acc[] = ['key' => $key, 'value' => $nodeFirst, 'type' => 'removed'];
                return $acc;
            }
            if ($nodeFirst === $nodeSecond) {
                $acc[] = ['key' => $key, 'value' => $nodeFirst, 'type' => 'former'];
                return $acc;
            } else {
                $acc[] = ['key' => $key, 'value' => $nodeSecond, 'type' => 'renewed'];
                $acc[] = ['key' => $key, 'value' => $nodeFirst, 'type' => 'removed'];
                return $acc;
            }
        }, []);
        return $diff;
    };
    $result = $iter($before, $after, []);
    return $result;
}

function chooseOutputsFormat($differences, $format)
{
    if ($format === 'pretty') {
        return renderDiff($differences);
    } elseif ($format === 'plain') {
        return renderPlainDiff($differences);
    } elseif ($format === 'json') {
        return renderJsonDiff($differences);
    }
}

function genDiff(string $pathFirst, string $pathSecond, $format)
{
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
    $differences = getDiff($firstData, $secondData);
    return chooseOutputsFormat($differences, $format);
}
