<?php

namespace App\CoinAdapters\Exceptions;

use Exception;

class AdapterException extends Exception
{
    /**
     * @var array
     */
    protected array $context = [];

    /**
     * Set context
     *
     * @param  array  $context
     * @return AdapterException
     */
    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the exception's context information.
     *
     * @return array
     */
    public function context(): array
    {
        return $this->context;
    }
}
