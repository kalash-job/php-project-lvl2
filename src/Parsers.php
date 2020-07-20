<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parseJson(string $jsonData)
{
    return json_decode($jsonData, false, 512, JSON_THROW_ON_ERROR);
}

function parseYaml(string $yamlData)
{
    return Yaml::parse($yamlData, Yaml::PARSE_OBJECT_FOR_MAP);
}
