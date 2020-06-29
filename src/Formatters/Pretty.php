<?php

namespace Differ\Pretty;

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
