<?php

namespace App\CoinAdapters\Resources;

class Address extends Resource
{
    /**
     * Address validation rules
     *
     * @var array|string[]
     */
    protected array $rules = [
        'id' => 'required|string',
        'label' => 'nullable|string',
        'address' => 'required|string',
        'data' => 'nullable|array',
    ];

    /**
     * Get address' id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->get('id');
    }

    /**
     * Get address' label
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->get('label');
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->get('address');
    }

    /**
     * Get data
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->get('data');
    }
}
