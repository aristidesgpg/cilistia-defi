<?php

namespace App\CoinAdapters\Resources;

class Wallet extends Resource
{
    /**
     * Wallet resource rules
     *
     * @var array|string[]
     */
    protected array $rules = [
        'id' => 'required|string',
        'data' => 'nullable|array',
    ];

    /**
     * Get source id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->get('id');
    }

    /**
     * Get wallet data
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->get('data');
    }
}
