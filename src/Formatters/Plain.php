<?php

namespace Differ\Plain;

function correctValue($value)
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
    $newValue = null;
    $iter = function ($path, $node) use (&$lines, &$iter, &$newValue) {
        if (isset($node['children'])) {
            $newPath = "{$path}{$node['key']}.";
            array_reduce($node['children'], $iter, $newPath);
            return $path;
        }
        $value = correctValue($node['value']);
        if ($node['type'] === 'added') {
            $lines[] = "Property '{$path}{$node['key']}' was added with value: {$value}";
            return $path;
        }
        if ($node['type'] === 'renewed') {
            $newValue = $value;
            return $path;
        }
        if ($node['type'] === 'removed') {
            if (is_null($newValue)) {
                $lines[] = "Property '{$path}{$node['key']}' was removed";
                return $path;
            }
            $lines[] = "Property '{$path}{$node['key']}' was changed. From {$value} to {$newValue}";
            $newValue = null;
            return $path;
        }
        return $path;
    };
    array_reduce($diff, $iter, '');
    $result = implode("\n", $lines);
    return "{$result}\n";
}
