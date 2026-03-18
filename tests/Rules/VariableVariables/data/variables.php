<?php

declare(strict_types=1);

function () {
    $a = 'foo';
    $foo = 'bar';
    echo $a;
    echo $foo;
    echo $$a;
};

function () {
    $a = 'foo';
    $$a = 'bar';
};
