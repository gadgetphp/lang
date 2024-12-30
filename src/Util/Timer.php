<?php

declare(strict_types=1);

namespace Gadget\Lang;

class Timer implements \Stringable
{
    private \DateTime|null $start = null;
    private \DateTime|null $stop = null;


    /**
     * @return $this
     */
    public function start(): self
    {
        return $this->getStart() === null
            ? $this->setStart(new \DateTime())->setStop(null)
            : $this;
    }


    /**
     * @return $this
     */
    public function stop(): static
    {
        return $this->getStart() !== null && $this->getStop() === null
            ? $this->setStop(new \DateTime())
            : $this;
    }


    /**
     * @return $this
     */
    public function reset(): static
    {
        return $this->setStart(null)->setStop(null);
    }


    /**
     * @return \DateTime|null
     */
    public function getStart(): \DateTime|null
    {
        return $this->start;
    }


    /**
     * @param \DateTime|null $start
     * @return $this
     */
    public function setStart(\DateTime|null $start): static
    {
        $this->start = $start;
        $this->setStop(null);
        return $this;
    }


    /**
     * @return \DateTime|null
     */
    public function getStop(): \DateTime|null
    {
        return $this->stop;
    }


    /**
     * @param \DateTime|null $stop
     * @return $this
     */
    public function setStop(\DateTime|null $stop): static
    {
        $this->stop = $this->getStart() !== null ? $stop : null;
        return $this;
    }


    /**
     * @return \DateInterval
     */
    public function getElapsed(): \DateInterval
    {
        $rightNow = new \DateTime();
        return ($this->getStart() ?? $rightNow)->diff($this->getStop() ?? $rightNow);
    }


    /**
     * @return string
     */
    public function __toString(): string
    {
        return substr($this->getElapsed()->format('%D:%H:%I:%S.%F'), 0, 15) ;
    }
}
