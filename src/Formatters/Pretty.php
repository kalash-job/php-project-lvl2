<?php

namespace Differ\Pretty;

use function Functional\flatten;

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

function stringify($value, int $depth): string
{
    if ($value === true) {
        return 'true';
    } elseif ($value === false) {
        return 'false';
    } elseif ($value === null) {
        return 'null';
    }
    if (!is_object($value)) {
        return $value;
    }
    $newLines =  array_map(function ($item) use ($depth) {
        $prefixSpaces = '    ';
        $offset = str_repeat('    ', $depth);
        $innerValue = stringify($item['value'], $depth + 1);
        return "{$offset}{$prefixSpaces}{$item['key']}: {$innerValue}";
    }, formatObject($value));
    $unionLine = implode("\n", $newLines);
    $lastOffset = str_repeat('    ', $depth - 1);
    return "{\n{$unionLine}\n{$lastOffset}    }";
}

function iter($node, int $depth)
{
    $prefixMinus = '  - ';
    $prefixPlus = '  + ';
    $prefixSpaces = '    ';
    $offset = str_repeat('    ', $depth - 1);
    switch ($node['type']) {
        case 'same':
            $prefix = $prefixSpaces;
            $value = stringify($node['value'], $depth);
            return "{$offset}{$prefix}{$node['key']}: {$value}";
        case 'added':
            $prefix = $prefixPlus;
            $value = stringify($node['value'], $depth);
            return "{$offset}{$prefix}{$node['key']}: {$value}";
        case 'removed':
            $prefix = $prefixMinus;
            $value = stringify($node['value'], $depth);
            return "{$offset}{$prefix}{$node['key']}: {$value}";
        case 'changed':
            $newValue = stringify($node['newValue'], $depth);
            $oldValue = stringify($node['oldValue'], $depth);
            $firstLine = "{$offset}{$prefixPlus}{$node['key']}: {$newValue}";
            $secondLine = "{$offset}{$prefixMinus}{$node['key']}: {$oldValue}";
            return "$firstLine\n$secondLine";
        case 'parent':
            $prefix = $prefixSpaces;
            $children = $node['children'];
            break;
        default:
            throw new \Exception("Unknown node type '{$node['type']}'");
    }
    $firstLine = ["{$offset}{$prefix}{$node['key']}: {"];
    $newLines = array_map(function ($child) use ($depth) {
        return iter($child, $depth + 1);
    }, $children);
    return array_merge($firstLine, $newLines, ["{$offset}    }"]);
}

function renderDiff(array $diff): string
{
    $startDepth = 1;
    $startLine = ["{"];
    $lastLine = ["}\n"];
    $lines = array_map(function ($node) use ($startDepth) {
        return iter($node, $startDepth);
    }, $diff);
    $linesFlattened = flatten($lines);
    return implode("\n", array_merge($startLine, $linesFlattened, $lastLine));
}
