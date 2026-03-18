<?php

declare(strict_types=1);

namespace UselessCastNotPhpDoc;

class Foo
{
    /**
     * @param int $phpDocInteger
     */
    public function doFoo(
        int $realInteger,
        $phpDocInteger
    ): void {
        $foo = (int) $realInteger;
        $bar = (int) $phpDocInteger;
    }

}
