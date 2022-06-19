<?php

namespace AmirHossein5\LaravelIpLogger;

trait Catchable
{
    /**
     * Notify throwed exception by inteded way.
     * 
     * @var ?\Closure 
     */
    private ?\Closure $catch = null;

    /**
     * Notify throwed exception by inteded way.
     * 
     * @param \Closure $closure
     * 
     * @return self
     */
    public function catch(\Closure $closure): self
    {
        $this->catch = $closure;

        return $this;
    }
}