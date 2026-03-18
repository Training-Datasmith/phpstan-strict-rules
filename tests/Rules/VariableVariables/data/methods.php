<?php

declare(strict_types=1);

function (stdClass $std) {
    $std->foo();

    $foo = 'bar';
    $std->$foo();
};
