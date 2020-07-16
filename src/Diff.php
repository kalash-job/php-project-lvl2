<?php

namespace Differ\Diff;

use function Differ\Parsers\parseJson;
use function Differ\Parsers\parseYaml;
use function Differ\Plain\renderPlainDiff;
use function Differ\Pretty\renderDiff;
use function Differ\Json\renderJsonDiff;

function getDiff($before, $after): array
{
        $firstColl = (array)$before;
        $secondColl = (array)$after;
        $keys = array_keys(array_merge($firstColl, $secondColl));
        return array_map(function ($key) use ($firstColl, $secondColl) {
            $nodeFirst = isset($firstColl[$key]) ? ($firstColl[$key]) : null;
            $nodeSecond = isset($secondColl[$key]) ? ($secondColl[$key]) : null;
            if (!isset($nodeFirst)) {
                return ['key' => $key, 'value' => $nodeSecond, 'type' => 'added'];
            } elseif (!isset($nodeSecond)) {
                return ['key' => $key, 'value' => $nodeFirst, 'type' => 'removed'];
            }
            if (is_object($nodeFirst) && is_object($nodeSecond)) {
                $children = getDiff($nodeFirst, $nodeSecond);
                return ['key' => $key, 'type' => 'former', 'children' => $children];
            } elseif (is_object($nodeFirst) || is_object($nodeSecond)) {
                return ['key' => $key, 'newValue' => $nodeSecond, 'oldValue' => $nodeFirst, 'type' => 'renewed'];
            }
            if ($nodeFirst === $nodeSecond) {
                return  ['key' => $key, 'value' => $nodeFirst, 'type' => 'former'];
            } else {
                return ['key' => $key, 'newValue' => $nodeSecond, 'oldValue' => $nodeFirst, 'type' => 'renewed'];
            }
        }, $keys);
}

function chooseOutputsFormat($differences, string $format)
{
    if ($format === 'pretty') {
        return renderDiff($differences);
    } elseif ($format === 'plain') {
        return renderPlainDiff($differences);
    } elseif ($format === 'json') {
        return renderJsonDiff($differences);
    }
}

function chooseParser(string $path)
{
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    if ($extension === 'json') {
        $data = parseJson(file_get_contents($path));
    } elseif ($extension === 'yaml' || $extension === 'yml') {
        $data = parseYaml(file_get_contents($path));
    } else {
        throw new \Exception("File $path must be in JSON or YAML format\n");
    }
    return $data;
}

function genDiff(string $pathFirst, string $pathSecond, string $format)
{
    if (!file_exists($pathFirst) || !file_exists($pathSecond)) {
        $filesName = !file_exists($pathFirst) ? $pathFirst : $pathSecond;

        throw new \Exception("File {$filesName} not found. You should write a correct path to the file\n");
    }
    $firstData = chooseParser($pathFirst);
    $secondData = chooseParser($pathSecond);
    $differences = getDiff($firstData, $secondData);
    return chooseOutputsFormat($differences, $format);
}
