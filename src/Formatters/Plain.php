<?php

namespace Differ\Plain;

function renderPlainDiff(array $diff): string
{
    $lines = [];
    $iter = function ($path, $node) use (&$lines, &$iter, &$newValue) {
        if (isset($node['children'])) {
            $newPath = "{$path}{$node['key']}.";
            array_reduce($node['children'], $iter, $newPath);
            return $path;
        }
        if ($node['type'] === 'added') {
            if (isset($node['value']) && is_object($node['value'])) {
                $lines[] = "Property '{$path}{$node['key']}' was added with value: 'complex value'";
                return $path;
            }
            $lines[] = "Property '{$path}{$node['key']}' was added with value: '{$node['value']}'";
            return $path;
        }
        if ($node['type'] === 'renewed') {
            $newValue = is_object($node['value']) ? 'complex value' : $node['value'];
            return $path;
        }
        if ($node['type'] === 'removed') {
            if (is_null($newValue)) {
                $lines[] = "Property '{$path}{$node['key']}' was removed";
                return $path;
            }
            $lines[] = "Property '{$path}{$node['key']}' was changed. From '{$node['value']}' to '{$newValue}'";
            $newValue = null;
            return $path;
        }
        return $path;
    };
    array_reduce($diff, $iter, '');
    $result = implode("\n", $lines);
    return "{$result}\n";
}
