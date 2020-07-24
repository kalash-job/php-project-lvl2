<?php

namespace Differ\Plain;

function stringify($value)
{
    if ($value === true) {
        return 'true';
    } elseif ($value === false) {
        return 'false';
    } elseif (is_object($value)) {
        return "'complex value'";
    } elseif (is_string($value)) {
        return "'{$value}'";
    }
    return $value;
}

function iter($node, string $path, array $lines): array
{
    if (isset($node['children'])) {
        $newPath = "{$path}{$node['key']}.";
        $lines = array_reduce($node['children'], function ($acc, $child) use ($newPath) {
            return iter($child, $newPath, $acc);
        }, $lines);
        return $lines;
    }
    if ($node['type'] === 'changed') {
        $oldValue = stringify($node['oldValue']);
        $newValue = stringify($node['newValue']);
        $lines[] = "Property '{$path}{$node['key']}' was changed. From {$oldValue} to {$newValue}";
        return $lines;
    }
    $value = stringify($node['value']);
    if ($node['type'] === 'added') {
        $lines[] = "Property '{$path}{$node['key']}' was added with value: {$value}";
        return $lines;
    }
    if ($node['type'] === 'removed') {
        $lines[] = "Property '{$path}{$node['key']}' was removed";
        return $lines;
    }
    return $lines;
}

function renderPlainDiff(array $diff): string
{
    $path = '';
    $lines = array_reduce($diff, function ($acc, $node) use ($path) {
        return iter($node, $path, $acc);
    }, []);
    $result = implode("\n", $lines);
    return "{$result}\n";
}
