<?php

declare(strict_types=1);

namespace Bug243;

function test(\SimpleXMLElement $xml)
{
    $xml->{'foo-bar'};
};
