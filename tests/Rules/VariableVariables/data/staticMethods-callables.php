<?php

declare(strict_types=1);
// lint >= 8.1

function (stdClass $std) {
    Foo::doFoo(...);

    $foo = 'doBar';
    Foo::$foo(...);

    $std::$foo(...);
};
