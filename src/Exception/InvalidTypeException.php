<?php

declare(strict_types=1);

namespace Gadget\Lang;

class InvalidTypeException extends Exception
{
    /**
     * @param string $expected
     * @param mixed $actual
     */
    public function __construct(
        string $expected,
        mixed $actual
    ) {
        parent::__construct([
            "Expected '%s', actual '%s'",
            $expected,
            gettype($actual)
        ]);
    }
}
