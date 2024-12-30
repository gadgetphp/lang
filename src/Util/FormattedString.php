<?php

declare(strict_types=1);

namespace Gadget\Lang;

class FormattedString implements \Stringable
{
    /**
     * @var list<string|\Stringable|int|float|bool|null> $values
     */
    private array $values = [];


    /**
     * @param string|\Stringable $format
     * @param string|\Stringable|int|float|bool|null ...$values
     */
    public function __construct(
        private string|\Stringable $format,
        string|\Stringable|int|float|bool|null ...$values
    ) {
        $this
            ->setFormat($format)
            ->setValues($values);
    }


    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format instanceof \Stringable
            ? $this->format->__toString()
            : $this->format;
    }


    /**
     * @param string|\Stringable $format
     * @return static
     */
    public function setFormat(string|\Stringable $format): static
    {
        $this->format = $format;
        return $this;
    }


    /**
     * @return list<string|\Stringable|int|float|bool|null> $values
     */
    public function getValues(): array
    {
        return $this->values;
    }


    /**
     * @param (string|\Stringable|int|float|bool|null)[] $values
     * @return static
     */
    public function setValues(array $values): static
    {
        $this->values = array_values($values);
        return $this;
    }


    /**
     * @param int $index
     * @return string|\Stringable|int|float|bool|null
     */
    public function getValue(int $index): string|\Stringable|int|float|bool|null
    {
        return isset($this->values[$index])
            ? $this->values[$index]
            : throw new OutOfBoundsException();
    }


    /**
     * @param string|\Stringable|int|float|bool|null $value
     * @param int|null $index
     * @return static
     */
    public function setValue(
        string|\Stringable|int|float|bool|null $value,
        int|null $index = null
    ): static {
        $values = &$this->values;
        if (is_int($index)) {
            $values[$index] = isset($this->values[$index]) ? $value : throw new OutOfBoundsException();
        } else {
            $values[] = $value;
        }
        return $this;
    }


    /**
     * @return string
     */
    public function format(): string
    {
        return sprintf($this->getFormat(), ...array_map(
            fn($v) => match (true) {
                $v instanceof \Stringable => $v->__toString(),
                $v === null => 'null',
                is_bool($v) => $v ? 'true' : 'false',
                default => $v
            },
            $this->getValues()
        ));
    }


    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
