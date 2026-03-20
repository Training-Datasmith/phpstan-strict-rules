<?php

declare(strict_types=1);

function (stdClass $std) {
    Foo::doFoo();

    $foo = 'doBar';
    Foo::$foo();

    $std::$foo();
};
