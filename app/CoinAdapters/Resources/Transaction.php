<?php

namespace App\CoinAdapters\Resources;

use App\CoinAdapters\Exceptions\AdapterException;
use App\CoinAdapters\Exceptions\ValidationException;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class Transaction extends Resource
{
    protected array $rules = [
        'id' => 'required|string',
        'hash' => 'required|string',
        'type' => 'required|in:send,receive',
        'value' => 'required|numeric',
        'confirmations' => 'required|numeric',
        'date' => 'required|string',
        'data' => 'nullable|array',
    ];

    /**
     * Transaction constructor.
     *
     * @param  array  $data
     *
     * @throws AdapterException
     * @throws ValidationException
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->validateAddress($this->getReceiver());
        $this->validateAddress($this->getSender());
    }

    /**
     * Get transaction id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->get('id');
    }

    /**
     * Get transaction hash
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->get('hash');
    }

    /**
     * Get date
     *
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return Carbon::parse($this->get('date'));
    }

    /**
     * Get confirmations
     *
     * @return int
     */
    public function getConfirmations(): int
    {
        return $this->get('confirmations');
    }

    /**
     * Get transaction value
     *
     * @return string
     */
    public function getValue(): string
    {
        return (string) BigDecimal::of($this->get('value'))->abs();
    }

    /**
     * Get sender
     *
     * @return array|string|null
     */
    public function getSender(): array|string|null
    {
        return $this->get('input') ?: $this->get('from');
    }

    /**
     * Get receiver
     *
     * @return array|string|null
     */
    public function getReceiver(): array|string|null
    {
        return $this->get('output') ?: $this->get('to');
    }

    /**
     * Get transaction type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->get('type');
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

    /**
     * Lock key for synchronization
     *
     * @return string
     */
    public function lockKey(): string
    {
        return $this->getHash();
    }

    /**
     * Validate inputs and outputs
     *
     * @param $address
     *
     * @throws AdapterException
     */
    protected function validateAddress($address)
    {
        if (!is_null($address) || $this->getType() !== 'send') {
            if (is_array($address)) {
                collect($address)->each(function ($item) {
                    if (!is_array($item) || !Arr::has($item, ['address', 'value'])) {
                        throw new AdapterException('Item must contain address, value pairs.');
                    }

                    if (!is_string($item['address']) || !is_numeric($item['value'])) {
                        throw new AdapterException('Item contains invalid address, value pairs');
                    }
                });
            } elseif (!is_string($address) || empty($address)) {
                throw new AdapterException('Invalid address provided.');
            }
        }
    }
}
