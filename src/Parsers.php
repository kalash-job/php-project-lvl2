<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $content, $format)
{
    if ($format === 'json') {
        return json_decode($content, false, 512, JSON_THROW_ON_ERROR);
    } elseif ($format === 'yaml' || $format === 'yml') {
        return Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
    } else {
        throw new \Exception("File must be in JSON or YAML format");
    }
}
