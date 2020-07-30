<?php

namespace Differ\Pretty;

function getPrefix($node)
{
    $prefixMinus = '  - ';
    $prefixPlus = '  + ';
    $prefixSpaces = '    ';
    if (!isset($node['type'])) {
        return $prefixSpaces;
    }
    if ($node['type'] === 'same' || $node['type'] === 'object') {
        return $prefixSpaces;
    } elseif ($node['type'] === 'added') {
        return $prefixPlus;
    } elseif ($node['type'] === 'parent') {
        return $prefixSpaces;
    } elseif ($node['type'] === 'changed') {
        return [$prefixPlus, $prefixMinus];
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
        $acc[] = ['key' => $key, 'value' => $value, 'type' => 'object'];
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

function iter($node, int $depth, array $lines): array
{
    $openingBracket = ': {';
    $closingBracket = '    }';
    if (isset($node['type'])) {
        $offset = str_repeat('    ', $depth - 1);
        $prefix = getPrefix($node);
        if ($node['type'] === 'parent') {
            $lines[] = "{$offset}{$prefix}{$node['key']}{$openingBracket}";
            $newDepth = $depth + 1;
            $lines = iter($node['value'], $newDepth, $lines);
            $lines[] = "{$offset}{$closingBracket}";
            return $lines;
        }
        if ($node['type'] !== 'changed' && is_object($node['value'])) {
            $lines[] = "{$offset}{$prefix}{$node['key']}{$openingBracket}";
            $value = formatObject($node['value']);
            $newDepth = $depth + 1;
            $lines = iter($value, $newDepth, $lines);
            $lines[] = "{$offset}{$closingBracket}";
            return $lines;
        }
        if ($node['type'] !== 'changed') {
            $value = stringify($node['value']);
            $lines[] = "{$offset}{$prefix}{$node['key']}: {$value}";
            return $lines;
        }
        $newValue = stringify($node['newValue']);
        $oldValue = stringify($node['oldValue']);
        $lines[] = "{$offset}{$prefix[0]}{$node['key']}: {$newValue}";
        $lines[] = "{$offset}{$prefix[1]}{$node['key']}: {$oldValue}";
        return $lines;
    }
    return array_reduce($node, function ($acc, $child) use ($depth) {
        return iter($child, $depth, $acc);
    }, $lines);
}

function renderDiff(array $diff): string
{
    $startDepth = 1;
    $startline = ["{"];
    $lines = iter($diff, $startDepth, $startline);
    $result = implode("\n", $lines);
    return "{$result}\n}\n";
}
