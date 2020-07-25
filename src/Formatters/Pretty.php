<?php

namespace Differ\Pretty;

use phpDocumentor\Reflection\Types\Integer;

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
    } elseif ($node['type'] === 'added' || $node['type'] === 'changed') {
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

function iter($node, int $depth, array $lines): array
{
    $openingBracket = ': {';
    $closingBracket = '    }';
    if (isset($node['type']) && $node['type'] === 'parent') {
        $offset = str_repeat('    ', $depth);
        $lines[] = "{$offset}{$node['key']}{$openingBracket}";
        $newDepth = $depth + 1;
        $lines = array_reduce($node['value'], function ($acc, $child) use ($newDepth) {
            return iter($child, $newDepth, $acc);
        }, $lines);
        $lines[] = "{$offset}}";
        return $lines;
    }
    $offset = str_repeat('    ', $depth - 1);
    $prefix = getPrefix($node);
    if (isset($node['value']) && is_object($node['value'])) {
        $lines[] = "{$offset}{$prefix}{$node['key']}{$openingBracket}";
        $value = formatObject($node['value']);
        $newDepth = $depth + 1;
        $lines = array_reduce($value, function ($acc, $child) use ($newDepth) {
            return iter($child, $newDepth, $acc);
        }, $lines);
        $lines[] = "{$offset}{$closingBracket}";
        return $lines;
    }
    if (isset($node['value'])) {
        if ($node['value'] === true) {
            $value = 'true';
        } elseif ($node['value'] === false) {
            $value = 'false';
        } else {
            $value = $node['value'];
        }
        $lines[] = "{$offset}{$prefix}{$node['key']}: {$value}";
        return $lines;
    }
    $lines[] = "{$offset}{$prefix}{$node['key']}: {$node['newValue']}";
    $lines[] = "{$offset}  - {$node['key']}: {$node['oldValue']}";
    return $lines;
}

function renderDiff(array $diff): string
{
    $startDepth = 1;
    $lines = array_reduce($diff, function ($acc, $node) use ($startDepth) {
        return iter($node, $startDepth, $acc);
    }, ["{"]);
    $result = implode("\n", $lines);
    //var_dump("{$result}\n}\n");
    return "{$result}\n}\n";
}
