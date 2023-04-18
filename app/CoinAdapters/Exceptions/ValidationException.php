<?php

namespace App\CoinAdapters\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\Validator;

class ValidationException extends Exception
{
    /**
     * The validator instance.
     *
     * @var Validator
     */
    protected Validator $validator;

    /**
     * The status code to use for the response.
     *
     * @var int
     */
    protected int $status = 500;

    /**
     * The data being validated
     *
     * @var array
     */
    protected array $data;

    /**
     * Create a new exception instance.
     *
     * @param  Validator  $validator
     */
    public function __construct($validator, array $data = [])
    {
        parent::__construct('The adapter resource is invalid.');

        $this->validator = $validator;
        $this->data = $data;
    }

    /**
     * Get all the validation error messages.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->validator->errors()->messages();
    }

    /**
     * Set the HTTP status code to be used for the response.
     *
     * @param  int  $status
     * @return $this
     */
    public function status(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the exception's context information.
     *
     * @return array
     */
    public function context(): array
    {
        return [
            'errors' => $this->errors(),
            'data' => $this->data,
        ];
    }
}
