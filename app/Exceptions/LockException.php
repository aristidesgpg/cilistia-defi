<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class LockException extends HttpException
{
    /**
     * Construct Lock Exception
     *
     * @param  string  $message
     */
    public function __construct(string $message = '')
    {
        parent::__construct(403, $message);
    }
}
