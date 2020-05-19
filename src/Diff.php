<?php

namespace Differ\Diff;

function getDiff(array $before, array $after): array
{
    $keys = array_keys(array_merge($before, $after));
    return array_reduce($keys, function ($acc, $key) use ($before, $after) {
        $prefixMinus = '- ';
        $prefixPlus = '+ ';
        $prefixSpace = '  ';
        $correctValue = function ($value) {
            if ($value === true) {
                return 'true';
            } elseif ($value === false) {
                return 'false';
            } else {
                return $value;
            }
        };
        if (isset($before[$key]) && isset($after[$key])) {
            if ($before[$key] === $after[$key]) {
                $acc[] = "$prefixSpace{$key}: {$before[$key]}";
                return $acc;
            }
            $acc[] = "{$prefixPlus}{$key}: {$correctValue($after[$key])}";
            $acc[] = "{$prefixMinus}{$key}: {$correctValue($before[$key])}";
            return $acc;
        } elseif (isset($before[$key])) {
            $acc[] = "{$prefixMinus}{$key}: {$correctValue($before[$key])}";
            return $acc;
        }
        $acc[] = "{$prefixPlus}{$key}: {$correctValue($after[$key])}";
        return $acc;
    }, []);
}

function parseJson(string $jsonData)
{
    $data = json_decode($jsonData, true);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $data_error  = '';
            break;
        case JSON_ERROR_DEPTH:
            $data_error  = " - Maximum stack depth exceeded\n";
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $data_error  = " - Underflow or the modes mismatch\n";
            break;
        case JSON_ERROR_CTRL_CHAR:
            $data_error  = " - Unexpected control character found\n";
            break;
        case JSON_ERROR_SYNTAX:
            $data_error  = " - Syntax error, malformed JSON\n";
            break;
        case JSON_ERROR_UTF8:
            $data_error  = " - Malformed UTF-8 characters, possibly incorrectly encoded\n";
            break;
        default:
            $data_error  = " - Unknown error\n";
            break;
    }
    if ($data_error !== '') {
        print_r($data_error);
    }
    return $data;
}

function genDiff(string $pathFirst, string $pathSecond, $format = null)
{
    if (!file_exists($pathFirst)) {
        return "You should write a correct path to the first file\n";
    } elseif (!file_exists($pathSecond)) {
        return "You should write a correct path to the second file\n";
    }
    $firstData = parseJson(file_get_contents($pathFirst));
    $secondData = parseJson(file_get_contents($pathSecond));
    $differences = implode("\n", getDiff($firstData, $secondData));
    return "{$differences}\n";
}
