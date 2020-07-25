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
    if (isset($node['type'])) {
        switch ($node['type']) {
            case 'parent':
                $newPath = "{$path}{$node['key']}.";
                $lines = iter($node['value'], $newPath, $lines);
                break;
            case 'changed':
                $oldValue = stringify($node['oldValue']);
                $newValue = stringify($node['newValue']);
                $lines[] = "Property '{$path}{$node['key']}' was changed. From {$oldValue} to {$newValue}";
                break;
            case 'added':
                $value = stringify($node['value']);
                $lines[] = "Property '{$path}{$node['key']}' was added with value: {$value}";
                break;
            case 'removed':
                $lines[] = "Property '{$path}{$node['key']}' was removed";
                break;
            case 'former':
                break;
            default:
                throw new \Exception("Unknown node type '{$node['type']}'");
        }
        return $lines;
    }
    return array_reduce($node, function ($acc, $newNode) use ($path) {
        return iter($newNode, $path, $acc);
    }, $lines);
}

function renderPlainDiff(array $diff): string
{
    $lines = iter($diff, '', []);
    $result = implode("\n", $lines);
    return "{$result}\n";
}
