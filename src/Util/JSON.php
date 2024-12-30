<?php

declare(strict_types=1);

namespace Gadget\Lang;

class JSON
{
    /** @var int<0,max> $flags */
    private int $flags = 0;

    /** @var int<1,2147483647> $depth */
    private int $depth = 512;


    /**
     * @return int<0,max>
     */
    public function getFlags(): int
    {
        return $this->flags;
    }


    /**
     * @param int<0,max> $flags
     * @return $this
     */
    public function setFlags(int $flags): static
    {
        $this->flags = $flags;
        return $this;
    }


    /**
     * @return int<1,2147483647>
     */
    public function getDepth(): int
    {
        return $this->depth;
    }


    /**
     * @param int<1,2147483647> $depth
     * @return $this
     */
    public function setDepth(int $depth): static
    {
        $this->depth = $depth;
        return $this;
    }


    /**
     * Decodes a JSON string
     *
     * @param string $json The json string being decoded.
     * @return mixed the value encoded in json in appropriate PHP type.
     */
    public function decode(string $json): mixed
    {
        return json_decode(
            $json,
            true,
            $this->getDepth(),
            $this->getFlags() | \JSON_THROW_ON_ERROR
        );
    }


    /**
     * Returns the JSON representation of a value
     *
     * @param mixed $value The value being encoded. Can be any type except a resource.
     * @return string a JSON-encoded string
     */
    public function encode(mixed $value): string
    {
        return json_encode(
            $value,
            $this->getFlags() | \JSON_THROW_ON_ERROR,
            $this->getDepth()
        );
    }
}
