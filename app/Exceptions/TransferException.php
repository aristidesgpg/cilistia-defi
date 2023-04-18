<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TransferException extends HttpException
{
    /**
     * Construct Transfer Exception
     *
     * @param  string  $message
     * @param  int  $statusCode
     */
    public function __construct(string $message = '', int $statusCode = 403)
    {
        parent::__construct($statusCode, $message);
    }
}
