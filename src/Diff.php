<?php

namespace Differ\Diff;

use function Differ\Parsers\parseJson;
use function Differ\Parsers\parseYaml;

/*function getDiff(array $before, array $after): array
{
    $keys = array_keys(array_merge($before, $after));
    return array_reduce($keys, function ($acc, $key) use ($before, $after) {
        $prefixMinus = '- ';
        $prefixPlus = '+ ';
        $prefixSpace = '  ';
        correctValue = function ($value) {
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
            $acc[] = "{$prefixPlus}{$key}: {correctValue($after[$key])}";
            $acc[] = "{$prefixMinus}{$key}: {correctValue($before[$key])}";
            return $acc;
        } elseif (isset($before[$key])) {
            $acc[] = "{$prefixMinus}{$key}: {correctValue($before[$key])}";
            return $acc;
        }
        $acc[] = "{$prefixPlus}{$key}: {correctValue($after[$key])}";
        return $acc;
    }, []);
}*/

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

// Функция получает отпарсенные объекты/массивы и возвращает массив с различиями, передаваемый в рендеринг
function getDiff($before, $after): array
{
    // анонимная функция
    $iter = function ($nodeBefore, $nodeAfter, $diff) use (&$iter) {
        // избавляемся от объектов на верхнем уровне сравниваемых объектов
        $firstColl = (array)$nodeBefore;
        $secondColl = (array)$nodeAfter;
        // получаем ключи верхнего уровня для последующего сравнения
        $keys = array_keys(array_merge($firstColl, $secondColl));
        // запускаем обход array_reduce по массиву с ключами, в acc собираем элементы с внутренним состоянием
        $diff = array_reduce($keys, function ($acc, $key) use ($nodeBefore, $nodeAfter, $firstColl, $secondColl, &$iter) {

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

function getPrefix($node): string
{
    $prefixMinus = '  - ';
    $prefixPlus = '  + ';
    $prefixSpaces = '    ';
    if (!isset($node['type'])) {
        return $prefixSpaces;
    }
    if ($node['type'] === 'former') {
        return $prefixSpaces;
    } elseif ($node['type'] === 'added' || $node['type'] === 'renewed') {
        return $prefixPlus;
    } else {
        return $prefixMinus;
    }
}

function formatObject(object $node): array
{
    $nodeAsColl = (array)$node;
    $keys = array_keys($nodeAsColl);
    return array_reduce($keys, function ($acc, $key) use ($nodeAsColl) {
        if (is_object($nodeAsColl[$key])) {
            $value = formatObject($nodeAsColl[$key]);
        } else {
            $value = $nodeAsColl[$key];
        }
        $acc[] = ['key' => $key, 'value' => $value];
        return $acc;
    }, []);
}

function renderDiff(array $diff): string
{
    $lines = ["{"];
    $iter = function ($depth, $node) use (&$lines, &$iter) {
        $openingBracket = ': {';
        $closingBracket = '    }';
        if (isset($node['children'])) {
            $offset = str_repeat('    ', $depth);
            $lines[] = "{$offset}{$node['key']}{$openingBracket}";
            array_reduce($node['children'], $iter, $depth + 1);
            $lines[] = "{$offset}}";
            return $depth;
        }
        if (isset($node['value']) && is_object($node['value'])) {
            $offset = str_repeat('    ', $depth - 1);
            $prefix = getPrefix($node);
            $lines[] = "{$offset}{$prefix}{$node['key']}{$openingBracket}";
            $value = formatObject($node['value']);
            array_reduce($value, $iter, $depth + 1);
            $lines[] = "{$offset}{$closingBracket}";
            return $depth;
        }
        if (isset($node['value'])) {
            $offset = str_repeat('    ', $depth - 1);
            $prefix = getPrefix($node);
            $lines[] = "{$offset}{$prefix}{$node['key']}: {$node['value']}";
            return $depth;
        }
    };
    $startDepth = 1;
    array_reduce($diff, $iter, $startDepth);
    $lines[] = "}\n";
    return implode("\n", $lines);
}


function genDiff(string $pathFirst, string $pathSecond, $format = null)
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
    return renderDiff($differences);
}
