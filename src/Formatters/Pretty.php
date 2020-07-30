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
    $type = isset($node['type']) ? $node['type'] : 'root';
    $offset = str_repeat('    ', $depth - 1);
    $renderStandartNode = function ($node, $prefix) use ($offset, $depth) {
        if (is_object($node['value']) || $node['type'] === 'parent') {
            $firstLine = ["{$offset}{$prefix}{$node['key']}: {"];
            $value = is_object($node['value']) ? formatObject($node['value']) : $node['value'];
            $newLines = iter($value, $depth + 1);
            return array_merge($firstLine, $newLines, ["{$offset}    }"]);
        } else {
            $value = stringify($node['value']);
            return ["{$offset}{$prefix}{$node['key']}: {$value}"];
        }
    };
    switch ($type) {
        case 'parent':
            return $renderStandartNode($node, $prefixSpaces);
        case 'same':
            return $renderStandartNode($node, $prefixSpaces);
        case 'added':
            return $renderStandartNode($node, $prefixPlus);
        case 'removed':
            return $renderStandartNode($node, $prefixMinus);
        case 'changed':
            $newValue = stringify($node['newValue']);
            $oldValue = stringify($node['oldValue']);
            $firstLine = ["{$offset}{$prefixPlus}{$node['key']}: {$newValue}"];
            $secondLine = ["{$offset}{$prefixMinus}{$node['key']}: {$oldValue}"];
            return array_merge($firstLine, $secondLine);
        case 'root':
            return array_reduce($node, function ($acc, $child) use ($depth) {
                return array_merge($acc, iter($child, $depth));
            }, []);
        default:
            throw new \Exception("Unknown node type '{$node['type']}'");
    }
}

function renderDiff(array $diff): string
{
    $startDepth = 1;
    $startLine = ["{"];
    $lines = iter($diff, $startDepth);
    $result = implode("\n", array_merge($startLine, $lines));
    return "{$result}\n}\n";
}
