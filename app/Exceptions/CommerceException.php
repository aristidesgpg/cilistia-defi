<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CommerceException extends HttpException
{
    /**
     * Construct Commerce Exception
     *
     * @param  string  $message
     * @param  int  $statusCode
     */
    public function __construct(string $message = '', int $statusCode = 422)
    {
        parent::__construct($statusCode, $message);
    }
}
