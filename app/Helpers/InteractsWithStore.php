<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnexpectedValueException;

trait InteractsWithStore
{
    /**
     * Store instance
     *
     * @var ValueStore
     */
    protected ValueStore $store;

    /**
     * Verification prefix
     *
     * @var string|null
     */
    protected ?string $prefix;

    /**
     * Initialize store helper
     *
     * @param  string|null  $prefix
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(string $prefix = null)
    {
        $this->prefix = $prefix;
        $this->store = valueStore();
        $this->initialize();
    }

    /**
     * Initialize children
     *
     * @return void
     */
    protected function initialize(): void
    {
        if (property_exists($this, 'children')) {
            collect($this->children)->each(function ($child, $key) {
                if (class_exists($child) && is_string($key)) {
                    $this->{$key} = new $child($this->key($key));
                }
            });
        }
    }

    /**
     * Get key name
     *
     * @param $name
     * @return string
     */
    protected function key($name): string
    {
        return Str::camel(is_string($this->prefix) ? "$this->prefix.$name" : $name);
    }

    /**
     * Put in store
     *
     * @param $name
     * @param $value
     * @return InteractsWithStore
     */
    public function put($name, $value): static
    {
        $this->validateName($name);

        $this->store->put($this->key($name), $value);

        return $this;
    }

    /**
     * Get from store
     *
     * @param $name
     * @return mixed
     */
    public function get($name): mixed
    {
        $default = $this->validateName($name);

        return $this->store->get($this->key($name), $default);
    }

    /**
     * Check if key exists in store
     *
     * @param $name
     * @return bool
     */
    public function has($name): bool
    {
        return $this->store->has($this->key($name));
    }

    /**
     * Validate settings name
     *
     * @param $name
     * @return  mixed
     */
    protected function validateName($name): mixed
    {
        $attributes = $this->attributes();

        if (!Arr::has($attributes, $name)) {
            throw new UnexpectedValueException("Unknown settings key: {$name}");
        }

        return Arr::get($attributes, $name);
    }

    /**
     * Get attributes
     *
     * @return array
     */
    protected function attributes(): array
    {
        return !property_exists($this, 'attributes') ? [] : $this->attributes;
    }

    /**
     * Get all values
     *
     * @return array
     */
    public function all(): array
    {
        return collect($this->attributes())->map(function ($default, $name) {
            return $this->store->get($this->key($name), $default);
        })->toArray();
    }
}
