<?php

namespace Differ\Plain;

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

function iter($node, string $path): array
{
    switch ($node['type']) {
        case 'changed':
            $oldValue = stringify($node['oldValue']);
            $newValue = stringify($node['newValue']);
            return ["Property '{$path}{$node['key']}' was changed. From {$oldValue} to {$newValue}"];
        case 'added':
            $value = stringify($node['value']);
            return ["Property '{$path}{$node['key']}' was added with value: {$value}"];
        case 'removed':
            return ["Property '{$path}{$node['key']}' was removed"];
        case 'same':
            return [];
        case 'parent':
            $newPath = "{$path}{$node['key']}.";
            break;
        default:
            throw new \Exception("Unknown node type '{$node['type']}'");
    }
    $children = $node['value'];
    return array_reduce($children, function ($acc, $child) use ($newPath) {
        return array_merge($acc, iter($child, $newPath));
    }, []);
}

function renderPlainDiff(array $diff): string
{
    $lines = array_reduce($diff, function ($acc, $node) {
        return array_merge($acc, iter($node, ''));
    }, []);
    $result = implode("\n", $lines);
    return "{$result}\n";
}
