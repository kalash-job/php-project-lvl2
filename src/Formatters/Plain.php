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

function renderPlainDiff(array $diff): string
{
    $lines = [];
    $iter = function ($path, $node) use (&$lines, &$iter) {
        if (isset($node['children'])) {
            $newPath = "{$path}{$node['key']}.";
            array_reduce($node['children'], $iter, $newPath);
            return $path;
        }
        if ($node['type'] === 'renewed') {
            $oldValue = stringify($node['oldValue']);
            $newValue = stringify($node['newValue']);
            $lines[] = "Property '{$path}{$node['key']}' was changed. From {$oldValue} to {$newValue}";
            return $path;
        }
        $value = stringify($node['value']);
        if ($node['type'] === 'added') {
            $lines[] = "Property '{$path}{$node['key']}' was added with value: {$value}";
            return $path;
        }
        if ($node['type'] === 'removed') {
            $lines[] = "Property '{$path}{$node['key']}' was removed";
            return $path;
        }
        return $path;
    };
    array_reduce($diff, $iter, '');
    $result = implode("\n", $lines);
    return "{$result}\n";
}
