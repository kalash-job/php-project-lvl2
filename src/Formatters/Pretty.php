<?php

namespace Differ\Pretty;

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

function iter($node, int $depth): array
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
                return ["{$offset}{$prefix}{$node['key']}: {$value}"];
            }
            break;
        case 'added':
            $prefix = $prefixPlus;
            if (!is_object($node['value'])) {
                $value = stringify($node['value']);
                return ["{$offset}{$prefix}{$node['key']}: {$value}"];
            }
            break;
        case 'removed':
            $prefix = $prefixMinus;
            if (!is_object($node['value'])) {
                $value = stringify($node['value']);
                return ["{$offset}{$prefix}{$node['key']}: {$value}"];
            }
            break;
        case 'changed':
            $newValue = stringify($node['newValue']);
            $oldValue = stringify($node['oldValue']);
            $firstLine = ["{$offset}{$prefixPlus}{$node['key']}: {$newValue}"];
            $secondLine = ["{$offset}{$prefixMinus}{$node['key']}: {$oldValue}"];
            return array_merge($firstLine, $secondLine);
        case 'parent':
            $prefix = $prefixSpaces;
            break;
        default:
            throw new \Exception("Unknown node type '{$node['type']}'");
    }

    $firstLine = ["{$offset}{$prefix}{$node['key']}: {"];
    $children = is_object($node['value']) ? formatObject($node['value']) : $node['value'];
    $newLines = array_reduce($children, function ($acc, $child) use ($depth) {
        return array_merge($acc, iter($child, $depth + 1));
    }, []);
    return array_merge($firstLine, $newLines, ["{$offset}    }"]);
}

function renderDiff(array $diff): string
{
    $startDepth = 1;
    $startLine = ["{"];
    $lines = array_reduce($diff, function ($acc, $node) use ($startDepth) {
        return array_merge($acc, iter($node, $startDepth));
    }, $startLine);
    $result = implode("\n", $lines);
    return "{$result}\n}\n";
}
