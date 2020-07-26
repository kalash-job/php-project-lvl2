<?php

namespace Differ\Diff;

use function Differ\Parsers\parse;
use function Differ\Plain\renderPlainDiff;
use function Differ\Pretty\renderDiff;
use function Differ\Json\renderJsonDiff;

function getDiff($before, $after): array
{
    $firstColl = (array)$before;
    $secondColl = (array)$after;
    $keys = array_keys(array_merge($firstColl, $secondColl));
    return array_map(function ($key) use ($firstColl, $secondColl) {
        if (!array_key_exists($key, $firstColl)) {
            return ['key' => $key, 'value' => $secondColl[$key], 'type' => 'added'];
        } elseif (!array_key_exists($key, $secondColl)) {
            return ['key' => $key, 'value' => $firstColl[$key], 'type' => 'removed'];
        }
        $nodeFirst = $firstColl[$key];
        $nodeSecond = $secondColl[$key];
        if (is_object($nodeFirst) && is_object($nodeSecond)) {
            $children = getDiff($nodeFirst, $nodeSecond);
            return ['key' => $key, 'type' => 'parent', 'value' => $children];
        }
        if ($nodeFirst === $nodeSecond) {
            return  ['key' => $key, 'value' => $nodeFirst, 'type' => 'former'];
        } else {
            return ['key' => $key, 'newValue' => $nodeSecond, 'oldValue' => $nodeFirst, 'type' => 'changed'];
        }
    }, $keys);
}

function render($differences, string $format)
{
    if ($format === 'pretty') {
        return renderDiff($differences);
    } elseif ($format === 'plain') {
        return renderPlainDiff($differences);
    } elseif ($format === 'json') {
        return renderJsonDiff($differences);
    }
}

function genDiff(string $pathFirst, string $pathSecond, string $format)
{
    if (!file_exists($pathFirst) || !file_exists($pathSecond)) {
        throw new \Exception("File not found. You should write a correct path to the file\n");
    }
    $contentFirst = file_get_contents($pathFirst);
    $contentSecond = file_get_contents($pathSecond);
    $extensionFirst = pathinfo($pathFirst, PATHINFO_EXTENSION);
    $extensionSecond = pathinfo($pathSecond, PATHINFO_EXTENSION);
    $firstData = parse($contentFirst, $extensionFirst);
    $secondData = parse($contentSecond, $extensionSecond);
    $differences = getDiff($firstData, $secondData);
    return render($differences, $format);
}
