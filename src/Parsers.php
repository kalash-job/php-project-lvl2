<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $filesContent, $filesFormat)
{
    if ($filesFormat === 'json') {
        return json_decode($filesContent, false, 512, JSON_THROW_ON_ERROR);
    } elseif ($filesFormat === 'yaml' || $filesFormat === 'yml') {
        return Yaml::parse($filesContent, Yaml::PARSE_OBJECT_FOR_MAP);
    } else {
        throw new \Exception("File $path must be in JSON or YAML format\n");
    }
}
