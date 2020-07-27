<?php

namespace Differ\Json;

function renderJsonDiff(array $diff): string
{
    return  json_encode($diff);
}
