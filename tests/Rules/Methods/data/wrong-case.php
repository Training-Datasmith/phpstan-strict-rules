<?php

declare(strict_types=1);

namespace WrongCase;

interface FooInterface
{
    public function getFoo();

}

class FooParent
{
    public function getBar()
    {

    }

}

class Foo extends FooParent implements FooInterface
{
    public function GETfoo()
    {

    }

    public function GETbar()
    {

    }

    public function getBaz()
    {

    }

}
