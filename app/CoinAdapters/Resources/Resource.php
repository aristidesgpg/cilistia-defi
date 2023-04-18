<?php

namespace App\CoinAdapters\Resources;

use App\CoinAdapters\Exceptions\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

abstract class Resource
{
    /**
     * Resource
     *
     * @var Collection
     */
    protected Collection $resource;

    /**
     * Validation rules
     *
     * @var array|string[]
     */
    protected array $rules;

    /**
     * Resource constructor.
     *
     * @param  array  $data
     *
     * @throws ValidationException
     */
    public function __construct(array $data)
    {
        $this->parse($data);
    }

    /**
     * Get resource value
     *
     * @param  string  $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->resource->get($key);
    }

    /**
     * @param  array  $data
     *
     * @throws ValidationException
     */
    protected function parse(array $data): void
    {
        $validator = Validator::make($data, $this->rules);

        if ($validator->fails()) {
            throw new ValidationException($validator, $data);
        }

        $this->resource = new Collection($data);
    }

    /**
     * Get json format
     *
     * @return string
     */
    public function toJson(): string
    {
        return $this->resource->toJson();
    }

    /**
     * Serialize resource
     *
     * @return array
     */
    public function __serialize(): array
    {
        return ["\0*\0resource" => $this->resource->toArray()];
    }

    /**
     * Unserialize resource from database
     *
     * @param  array  $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->resource = collect($data["\0*\0resource"]);
    }
}
