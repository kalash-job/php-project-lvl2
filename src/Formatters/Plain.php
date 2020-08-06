<?php

namespace Differ\Plain;

use function Functional\flatten;

function stringify($value)
{
    if ($value === true) {
        return 'true';
    } elseif ($value === false) {
        return 'false';
    } elseif ($value === null) {
        return 'null';
    } elseif (is_object($value)) {
        return "'complex value'";
    } elseif (is_string($value)) {
        return "'{$value}'";
    }
    return $value;
}

function iter($node, string $path)
{
    switch ($node['type']) {
        case 'changed':
            $oldValue = stringify($node['oldValue']);
            $newValue = stringify($node['newValue']);
            return "Property '{$path}{$node['key']}' was changed. From {$oldValue} to {$newValue}";
        case 'added':
            $value = stringify($node['value']);
            return "Property '{$path}{$node['key']}' was added with value: {$value}";
        case 'removed':
            return "Property '{$path}{$node['key']}' was removed";
        case 'same':
            return [];
        case 'parent':
            $newPath = "{$path}{$node['key']}.";
            $children = $node['children'];
            break;
        default:
            throw new \Exception("Unknown node type '{$node['type']}'");
    }
    return array_map(function ($child) use ($newPath) {
        return iter($child, $newPath);
    }, $children);
}

function renderPlainDiff(array $diff): string
{
    $lines = array_map(function ($node) {
        return iter($node, '');
    }, $diff);
    $linesFlattened = flatten($lines);
    $result = implode("\n", $linesFlattened);
    return "{$result}\n";
}
