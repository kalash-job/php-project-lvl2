<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

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
        throw new \Exception("Error of JSON encoding: {$data_error}\n");
    }
    return $data;
}

function parseYaml(string $yamlData)
{
    return Yaml::parse($yamlData);
}
