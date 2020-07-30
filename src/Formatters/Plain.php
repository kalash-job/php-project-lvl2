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
    $type = isset($node['type']) ? $node['type'] : 'root';
    switch ($type) {
        case 'parent':
            $newPath = "{$path}{$node['key']}.";
            return iter($node['value'], $newPath);
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
        case 'root':
            return array_reduce($node, function ($acc, $child) use ($path) {
                return array_merge($acc, iter($child, $path));
            }, []);
        default:
            throw new \Exception("Unknown node type '{$node['type']}'");
    }
}

function renderPlainDiff(array $diff): string
{
    $lines = iter($diff, '');
    $result = implode("\n", $lines);
    return "{$result}\n";
}
