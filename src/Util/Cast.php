<?php

declare(strict_types=1);

namespace Gadget\Lang;

class Cast
{
    private const BOOL_VALUES = ['1', 'O', 'T', 'X', 'Y'];


    /**
     * @param JSON $json
     */
    public function __construct(private JSON $json = new JSON())
    {
    }


    /**
     * @param string $expected
     * @param mixed $value
     * @return \Throwable
     */
    protected function err(
        string $expected,
        mixed $value
    ): \Throwable {
        return new InvalidTypeException($expected, $value);
    }


    /**
     * @param mixed $value
     * @return mixed
     */
    public function fromJSON(mixed $value): mixed
    {
        return is_string($value)
            ? $this->json->decode($value)
            : $value;
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public function toBool(mixed $value): bool
    {
        return $this->toBoolOrNull($value)
            ?? throw $this->err('boolean', $value);
    }


    /**
     * @param mixed $value
     * @return bool|null
     */
    public function toBoolOrNull(mixed $value): bool|null
    {
        return match (true) {
            is_bool($value) || $value === null => $value,
            is_scalar($value) || $value instanceof \Stringable => in_array(
                strtoupper(substr(strval($value), 0, 1)),
                self::BOOL_VALUES,
                true
            ),
            default => throw $this->err('boolean', $value)
        };
    }


    /**
     * @param mixed $value
     * @return float
     */
    public function toFloat(mixed $value): float
    {
        return $this->toFloatOrNull($value)
            ?? throw $this->err('float', $value);
    }


    /**
     * @param mixed $value
     * @return float|null
     */
    public function toFloatOrNull(mixed $value): float|null
    {
        return match (true) {
            is_float($value) || $value === null => $value,
            is_scalar($value) => floatval($value),
            $value instanceof \Stringable => floatval($value->__toString()),
            default => throw $this->err('float', $value)
        };
    }


    /**
     * @param mixed $value
     * @return int
     */
    public function toInt(mixed $value): int
    {
        return $this->toIntOrNull($value)
            ?? throw $this->err('integer', $value);
    }


    /**
     * @param mixed $value
     * @return int|null
     */
    public function toIntOrNull(mixed $value): int|null
    {
        return match (true) {
            is_int($value) || $value === null => $value,
            is_scalar($value) => intval($value),
            $value instanceof \Stringable => intval($value->__toString()),
            default => throw $this->err('integer', $value)
        };
    }


    /**
     * @param mixed $value
     * @return string
     */
    public function toString(mixed $value): string
    {
        return $this->toStringOrNull($value)
            ?? throw $this->err('string', $value);
    }


    /**
     * @param mixed $value
     * @return string|null
     */
    public function toStringOrNull(mixed $value): string|null
    {
        return match (true) {
            is_string($value) || $value === null => $value,
            is_scalar($value) || $value instanceof \Stringable => strval($value),
            default => throw $this->err('string', $value)
        };
    }


    /**
     * @param mixed $value
     * @return mixed[]
     */
    public function toArray(mixed $value): array
    {
        return $this->toArrayOrNull($value)
            ?? throw $this->err('array', $value);
    }


    /**
     * @param mixed $value
     * @return mixed[]|null
     */
    public function toArrayOrNull(mixed $value): array|null
    {
        $value = $this->fromJSON($value);
        return match (true) {
            is_array($value) || $value === null => $value,
            is_object($value) => get_object_vars($value),
            default => throw $this->err('array', $value)
        };
    }


    /**
     * @template TCastValue
     * @param mixed $values
     * @param (callable(mixed $value):TCastValue) $toValue
     * @return TCastValue[]
     */
    public function toTypedArray(
        mixed $values,
        callable $toValue
    ): array {
        return $this->toTypedArrayOrNull($values, $toValue)
            ?? throw $this->err('array', $values);
    }


    /**
     * @template TCastValue
     * @param mixed $values
     * @param (callable(mixed $value):TCastValue) $toValue
     * @return TCastValue[]|null
     */
    public function toTypedArrayOrNull(
        mixed $values,
        callable $toValue
    ): array|null {
        $values = $this->toArrayOrNull($values);
        return $values !== null
            ? array_map(
                $toValue,
                array_values($values)
            )
            : null;
    }


    /**
     * @template TCastValue
     * @param mixed $values
     * @param (callable(mixed $value):TCastValue) $toValue
     * @param (callable(TCastValue $value):string) $key
     * @return array<string,TCastValue>
     */
    public function toTypedMap(
        mixed $values,
        callable $toValue,
        callable $key
    ): array {
        return $this->toTypedMapOrNull($values, $toValue, $key)
            ?? throw $this->err('array', $values);
    }


    /**
     * @template TCastValue
     * @param mixed $values
     * @param (callable(mixed $value):TCastValue) $toValue
     * @param (callable(TCastValue $value):string) $key
     * @return array<string,TCastValue>|null
     */
    public function toTypedMapOrNull(
        mixed $values,
        callable $toValue,
        callable $key
    ): array|null {
        /**
         * @param TCastValue $v
         * @return array{TCastValue,string}
         */
        $keyMap = fn(mixed $v): array => [$v, $key($v)];
        $values = $this->toTypedArrayOrNull($values, $toValue);

        return $values !== null
            ? array_column(
                array_map(
                    $keyMap,
                    $values
                ),
                0,
                1
            )
            : null;
    }


    /**
     * @template TCastObject of object
     * @param mixed $values
     * @param (callable(mixed $values):TCastObject) $factory
     * @return TCastObject
     */
    public function toObject(
        mixed $values,
        callable $factory
    ): object {
        return $this->toObjectOrNull($values, $factory)
            ?? throw $this->err('object', $values);
    }


    /**
     * @template TCastObject of object
     * @param mixed $values
     * @param (callable(mixed $values):TCastObject) $factory
     * @return TCastObject|null
     */
    public function toObjectOrNull(
        mixed $values,
        callable $factory
    ): object|null {
        $values = $this->fromJSON($values);
        return $values !== null
            ? $factory($values)
            : null;
    }


    /**
     * @template TCastValue
     * @param mixed $value
     * @param (callable(mixed $value):TCastValue) $toValue
     * @return TCastValue|null
     */
    public static function toValueOrNull(
        mixed $value,
        callable $toValue
    ): mixed {
        return $value !== null ? $toValue($value) : $value;
    }
}
