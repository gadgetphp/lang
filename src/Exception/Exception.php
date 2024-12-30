<?php

declare(strict_types=1);

namespace Gadget\Lang;

class Exception extends \Exception
{
    /**
     * @param string|\Stringable|array{
     *   0: string,
     *   ...<int,string|\Stringable|int|float>
     * } $message
     * @param \Throwable|null $previous
     */
    public function __construct(
        string|\Stringable|array $message = "",
        \Throwable|null $previous = null,
        int $code = 0
    ) {
        parent::__construct("", 0, $previous);
        $this->setMessage($message)->setCode($code);
    }


    /**
     * @param string|\Stringable|array{
     *   0: string,
     *   ...<int,string|int|float>
     * } $message
     * @return $this
     */
    public function setMessage(string|\Stringable|array $message): static
    {
        $this->message = match (true) {
            is_array($message) => sprintf(...$message),
            $message instanceof \Stringable => $message->__toString(),
            default => $message
        };
        return $this;
    }


    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code): static
    {
        $this->code = $code;
        return $this;
    }
}
