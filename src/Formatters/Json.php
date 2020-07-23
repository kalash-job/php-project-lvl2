<?php

namespace Differ\Json;

function renderJsonDiff(array $diff): string
{
    $iter = function ($acc, $node) use (&$iter) {
        if (isset($node['children'])) {
            $children = array_reduce($node['children'], $iter, []);
            $acc[$node['key']] = $children;
            return $acc;
        }
        if ($node['type'] === 'added') {
            $value = ['type' => 'added', 'addingValue' => $node['value']];
            $acc[$node['key']] = [$value];
            return $acc;
        }
        if ($node['type'] === 'former') {
            $acc[$node['key']] = $node['value'];
            return $acc;
        }
        if ($node['type'] === 'removed') {
            $value = ['type' => 'removed', 'removingValue' => $node['value']];
            $acc[$node['key']] = [$value];
            return $acc;
        }
        if ($node['type'] === 'changed') {
            $value = ['type' => 'changed', 'newValue' => $node['newValue'], 'oldValue' => $node['oldValue']];
            $acc[$node['key']] = [$value];
            return $acc;
        }
    };
    $result = json_encode(array_reduce($diff, $iter, []));
    return "{$result}\n";
}
