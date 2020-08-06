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
        $acc[] = ['key' => $key, 'value' => $value, 'type' => 'same'];
        return $acc;
    }, []);
}

function stringify($value)
{
    if ($value === true) {
        return 'true';
    } elseif ($value === false) {
        return 'false';
    } elseif ($value === null) {
        return 'null';
    }
    return $value;
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
            if (!is_object($node['value'])) {
                $value = stringify($node['value']);
                return "{$offset}{$prefix}{$node['key']}: {$value}";
            }
            $children = formatObject($node['value']);
            break;
        case 'added':
            $prefix = $prefixPlus;
            if (!is_object($node['value'])) {
                $value = stringify($node['value']);
                return "{$offset}{$prefix}{$node['key']}: {$value}";
            }
            $children = formatObject($node['value']);
            break;
        case 'removed':
            $prefix = $prefixMinus;
            if (!is_object($node['value'])) {
                $value = stringify($node['value']);
                return "{$offset}{$prefix}{$node['key']}: {$value}";
            }
            $children = formatObject($node['value']);
            break;
        case 'changed':
            $newValue = stringify($node['newValue']);
            $oldValue = stringify($node['oldValue']);
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
