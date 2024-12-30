<?php

declare(strict_types=1);

namespace Gadget\Lang;

/**
 * @implements \IteratorAggregate<string>
 */
class StackTraceIterator implements \IteratorAggregate
{
    /**
     * @var array<string,string> $seen
     */
    private array $seen = [];


    /**
     * @param \Throwable $ex
     */
    public function __construct(private \Throwable $ex)
    {
    }


    /**
     * @return \Traversable<string>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->getStackTraceDetail($this->ex);
    }


    /**
     * @param \Throwable|null $ex
     * @return iterable<string>
     */
    private function getStackTraceDetail(\Throwable|null $ex): iterable
    {
        if ($ex === null) {
            return;
        }

        yield $this->writeException($ex);

        $trace = $this->getTrace($ex);
        $remaining = count($trace);
        foreach ($trace as list($current, $file, $line, $class, $function)) {
            if (isset($this->seen[$current])) {
                yield " ... {$remaining} more";
                break;
            }

            $this->seen[$current] = $current;
            yield $this->writeTrace($file, $line, $class, $function);
            $remaining--;
        }

        yield from $this->getStackTraceDetail($ex->getPrevious());
    }


    /**
     * @param \Throwable $ex
     * @return string
     */
    private function writeException(\Throwable $ex): string
    {
        $causedBy = count($this->seen) > 0 ? 'Caused by: ' :  '';
        $exClass = get_class($ex);
        $exMessage = $ex->getMessage();
        return "{$causedBy}{$exClass}: {$exMessage}";
    }


    /**
     * @param string $file
     * @param int $line
     * @param string|null $class
     * @param string $function
     * @return string
     */
    private function writeTrace(
        string $file,
        int $line,
        string|null $class,
        string $function
    ): string {
        $file = $line > 0 ? basename($file) : $file;
        $line = $line > 0 ? ":{$line}" : 0;
        $class = is_string($class) ? str_replace('\\', '.', $class) . '.' : '';
        $function = str_replace('\\', '.', $function);
        return " at {$class}{$function}({$file}{$line})";
    }


    /**
     * @param \Throwable $ex
     * @return array{string,string,int,string|null,string}[]
     */
    private function getTrace(\Throwable $ex): array
    {
        /** @var array{string,int}[] $fileLine */
        $fileLine = [[$ex->getFile(), $ex->getLine()]];
        /** @var array{string|null,string}[] $classFunction */
        $classFunction = [];

        $trace = $ex->getTrace();
        foreach ($trace as $t) {
            $fileLine[] = [$t['file'] ?? '', $t['line'] ?? 0];
            $classFunction[] = [$t['class'] ?? null, $t['function']];
        }
        $classFunction[] = [null, '{main}'];

        return array_map(
            fn(array $fl, array $cf) => [$fl[0] . ':' . $fl[1], $fl[0], $fl[1], $cf[0], $cf[1]],
            $fileLine,
            $classFunction
        );
    }
}
