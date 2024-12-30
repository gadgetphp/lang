<?php

declare(strict_types=1);

namespace Gadget\Lang;

class Base32
{
    private int $bits5Right = 31;
    private string $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789';


    public function getBits5Right(): int
    {
        return $this->bits5Right;
    }


    /**
     * @param int $bits5Right
     * @return $this
     */
    public function setBits5Right(int $bits5Right): static
    {
        $this->bits5Right = $bits5Right;
        return $this;
    }


    /**
     * @param int|null $c
     * @return string
     */
    public function getChars(int|null $c = null): string
    {
        return $c !== null
            ? ($this->chars[$c] ?? '')
            : $this->chars;
    }


    /**
     * @param string $chars
     * @return $this
     */
    public function setChars(string $chars): static
    {
        $this->chars = $chars;
        return $this;
    }


    /**
     * @param string $data
     * @param bool $padRight
     * @return string
     */
    public function encode(
        string $data,
        bool $padRight = false
    ): string {
        $dataSize = strlen($data);
        $res = '';
        $remainder = 0;
        $remainderSize = 0;

        for ($i = 0; $i < $dataSize; ++$i) {
            $b = ord($data[$i]);
            $remainder = ($remainder << 8) | $b;
            $remainderSize += 8;

            while ($remainderSize > 4) {
                $remainderSize -= 5;
                $c = $remainder & ($this->getBits5Right() << $remainderSize);
                $c >>= $remainderSize;
                $res .= $this->getChars($c);
            }
        }

        if ($remainderSize > 0) {
            $remainder <<= (5 - $remainderSize);
            $c = $remainder & $this->getBits5Right();
            $res .= $this->getChars($c);
        }

        if ($padRight) {
            $padSize = (8 - ceil(($dataSize % 5) * 8 / 5)) % 8;
            $res .= str_repeat('=', $padSize);
        }

        return $res;
    }


    /**
     * @param string $data
     * @return string
     */
    public function decode(string $data): string
    {
        $charMap = array_flip(str_split($this->getChars()));
        $charMap += array_flip(str_split(strtolower($this->getChars())));

        $data = rtrim($data, "=\x20\t\n\r\0\x0B");
        $dataSize = strlen($data);
        $buf = 0;
        $bufSize = 0;
        $res = '';

        for ($i = 0; $i < $dataSize; ++$i) {
            $c = $data[$i];
            $b = $charMap[$c];
            $buf = ($buf << 5) | $b;
            $bufSize += 5;
            if ($bufSize > 7) {
                $bufSize -= 8;
                $b = ($buf & (0xff << $bufSize)) >> $bufSize;
                $res .= chr($b);
            }
        }

        return $res;
    }
}
