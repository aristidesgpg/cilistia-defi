<?php

namespace App\Models;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Exceptions\LockException;
use App\Exceptions\TransferException;
use App\Models\Support\Lock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use UnexpectedValueException;

class PaymentAccount extends Model
{
    use HasFactory, Lock;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['reference', 'currency'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['user', 'supportedCurrency'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['user'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_balance',
        'formatted_balance_on_trade',
        'formatted_available',
        'formatted_total_received',
        'formatted_total_pending_receive',
        'formatted_total_sent',
        'symbol',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (self $record) {
            $record->assignReference();
        });
    }

    /**
     * Check if reference does not exist
     *
     * @param  string  $reference
     * @return bool
     */
    protected static function doesntHaveReference(string $reference): bool
    {
        return static::withoutGlobalScopes()->where('reference', $reference)->doesntExist();
    }

    /**
     * Parse money from input
     *
     * @param $amount
     * @return Money
     */
    public function parseMoney($amount): Money
    {
        return $this->supportedCurrency->parseMoney($amount);
    }

    /**
     * Assign unique reference
     *
     * @return void
     */
    protected function assignReference(): void
    {
        while (!$this->reference || !static::doesntHaveReference($this->reference)) {
            $this->reference = strtoupper(Str::random(10));
        }
    }

    /**
     * Min Transferable Object
     *
     * @return Attribute
     */
    protected function minTransferable(): Attribute
    {
        return Attribute::get(function (): Money {
            if (!$this->supportedCurrency->min_amount) {
                $value = Money::USD(settings()->get('min_payment'), true);

                return exchanger($value, new Currency($this->currency));
            } else {
                return $this->supportedCurrency->min_amount;
            }
        });
    }

    /**
     * Max Transferable Object
     *
     * @return Attribute
     */
    protected function maxTransferable(): Attribute
    {
        return Attribute::get(function (): Money {
            if (!$this->supportedCurrency->max_amount) {
                $value = Money::USD(settings()->get('max_payment'), true);

                return exchanger($value, new Currency($this->currency));
            } else {
                return $this->supportedCurrency->max_amount;
            }
        });
    }

    /**
     * Available Object
     *
     * @return Money
     */
    public function getAvailableObject(): Money
    {
        return $this->available;
    }

    /**
     * Available
     *
     * @return Attribute
     */
    protected function available(): Attribute
    {
        return Attribute::get(function (): Money {
            return $this->getBalanceObject()->subtract($this->getBalanceOnTradeObject());
        });
    }

    /**
     * Formatted available
     *
     * @return string
     */
    protected function getFormattedAvailableAttribute(): string
    {
        return $this->getAvailableObject()->format();
    }

    /**
     * Balance On Trade Object
     *
     * @return Money
     */
    public function getBalanceOnTradeObject(): Money
    {
        return $this->balance_on_trade;
    }

    /**
     * Balance On Trade
     *
     * @return Attribute
     */
    protected function balanceOnTrade(): Attribute
    {
        return Attribute::get(function (): Money {
            $exchangeTrade = $this->exchangeTrades()->where('type', 'buy')
                ->where('status', 'pending')->sum('payment_value');

            return $this->supportedCurrency->castMoney($exchangeTrade);
        });
    }

    /**
     * Formatted balance on trade
     *
     * @return string
     */
    protected function getFormattedBalanceOnTradeAttribute(): string
    {
        return $this->getBalanceOnTradeObject()->format();
    }

    /**
     * Balance Object
     *
     * @return Money
     */
    public function getBalanceObject(): Money
    {
        return $this->balance;
    }

    /**
     * Balance
     *
     * @return Attribute
     */
    protected function balance(): Attribute
    {
        return Attribute::get(function (): Money {
            return $this->getTotalReceivedObject()->subtract($this->getTotalSentObject());
        });
    }

    /**
     * Get formatted balance object
     *
     * @return string
     */
    protected function getFormattedBalanceAttribute(): string
    {
        return $this->getBalanceObject()->format();
    }

    /**
     * Total sent query
     *
     * @return HasMany
     */
    protected function totalSentQuery(): HasMany
    {
        return $this->transactions()
            ->where('status', '!=', 'canceled')
            ->where('type', 'send');
    }

    /**
     * Total Sent object
     *
     * @return Money
     */
    public function getTotalSentObject(): Money
    {
        return $this->total_sent;
    }

    /**
     * Total Sent
     *
     * @return Attribute
     */
    protected function totalSent(): Attribute
    {
        return Attribute::get(function (): Money {
            $total = $this->totalSentQuery()->sum('value');

            return $this->supportedCurrency->castMoney($total);
        });
    }

    /**
     * Format total sent
     *
     * @return string
     */
    protected function getFormattedTotalSentAttribute(): string
    {
        return $this->getTotalSentObject()->format();
    }

    /**
     * Total received query
     *
     * @return HasMany
     */
    protected function totalReceivedQuery(): HasMany
    {
        return $this->transactions()
            ->where('status', 'completed')
            ->where('type', 'receive');
    }

    /**
     * Total Received Object
     *
     * @return Money
     */
    public function getTotalReceivedObject(): Money
    {
        return $this->total_received;
    }

    /**
     * Total received
     *
     * @return Attribute
     */
    protected function totalReceived(): Attribute
    {
        return Attribute::get(function (): Money {
            $total = $this->totalReceivedQuery()->sum('value');

            return $this->supportedCurrency->castMoney($total);
        });
    }

    /**
     * Get formatted Total received.
     *
     * @return string
     */
    protected function getFormattedTotalReceivedAttribute(): string
    {
        return $this->getTotalReceivedObject()->format();
    }

    /**
     * Total pending receive query
     *
     * @return HasMany
     */
    protected function totalPendingReceiveQuery(): HasMany
    {
        return $this->transactions()
            ->whereIn('status', ['pending-transfer', 'pending-gateway'])
            ->where('type', 'receive');
    }

    /**
     * Has maximum pending
     *
     * @return bool
     */
    public function hasMaximumPending(): bool
    {
        return $this->totalPendingReceiveQuery()->count() > 2;
    }

    /**
     * Total pending receive object
     *
     * @return Money
     */
    public function getTotalPendingReceiveObject(): Money
    {
        return $this->total_pending_receive;
    }

    /**
     * Total pending receive
     *
     * @return Attribute
     */
    protected function totalPendingReceive(): Attribute
    {
        return Attribute::get(function (): Money {
            $total = $this->totalPendingReceiveQuery()->sum('value');

            return $this->supportedCurrency->castMoney($total);
        });
    }

    /**
     * Get formatted total pending receive.
     *
     * @return string
     */
    protected function getFormattedTotalPendingReceiveAttribute(): string
    {
        return $this->getTotalPendingReceiveObject()->format();
    }

    /**
     * Get referenced user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get payment transaction
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'payment_account_id', 'id');
    }

    /**
     * Supported currency
     *
     * @return BelongsTo
     */
    public function supportedCurrency(): BelongsTo
    {
        return $this->belongsTo(SupportedCurrency::class, 'currency', 'code');
    }

    /**
     * Related exchange trades
     *
     * @return HasMany
     */
    public function exchangeTrades(): HasMany
    {
        return $this->hasMany(ExchangeTrade::class, 'payment_account_id', 'id');
    }

    /**
     * Get symbol attribute
     *
     * @return string
     */
    protected function getSymbolAttribute(): string
    {
        return $this->supportedCurrency->symbol;
    }

    /**
     * Credit account
     *
     * @param  Money  $amount
     * @param  string  $description
     * @return PaymentTransaction
     */
    public function credit(Money $amount, string $description): PaymentTransaction
    {
        $value = $this->validateAmount($amount);

        return $this->transactions()->create([
            'type' => 'receive',
            'status' => 'completed',
            'description' => $description,
            'value' => $value,
        ]);
    }

    /**
     * Debit account
     *
     * @param  Money  $amount
     * @param  string  $description
     * @return PaymentTransaction
     */
    public function debit(Money $amount, string $description): PaymentTransaction
    {
        $value = $this->validateAmount($amount);

        return $this->transactions()->create([
            'type' => 'send',
            'status' => 'completed',
            'description' => $description,
            'value' => $value,
        ]);
    }

    /**
     * Create withdrawal request
     *
     * @param  Money  $amount
     * @param  BankAccount  $bankAccount
     * @return PaymentTransaction
     *
     * @throws LockException
     */
    public function sendViaTransfer(Money $amount, BankAccount $bankAccount): PaymentTransaction
    {
        $value = $this->validateAmount($amount);

        return $this->acquireLockOrThrow(function (self $account) use ($value, $bankAccount) {
            if ($account->getAvailableObject()->lessThan($value)) {
                throw new TransferException(trans('payment.insufficient_balance'));
            }

            return $account->transactions()->create([
                'type' => 'send',
                'status' => 'pending-transfer',
                'value' => $value,
                'description' => $bankAccount->getTransferDescription(),
                'transfer_bank' => $bankAccount->bank_name,
                'transfer_beneficiary' => $bankAccount->beneficiary,
                'transfer_number' => $bankAccount->number,
                'transfer_country' => $bankAccount->country,
                'transfer_note' => $bankAccount->note,
            ]);
        });
    }

    /**
     * Create transfer receive
     *
     * @param  Money  $amount
     * @param  BankAccount  $bankAccount
     * @return PaymentTransaction
     */
    public function receiveViaTransfer(Money $amount, BankAccount $bankAccount): PaymentTransaction
    {
        $value = $this->validateAmount($amount);

        return $this->transactions()->create([
            'type' => 'receive',
            'status' => 'pending-transfer',
            'value' => $value,
            'description' => $bankAccount->getTransferDescription(),
            'transfer_bank' => $bankAccount->bank_name,
            'transfer_beneficiary' => $bankAccount->beneficiary,
            'transfer_number' => $bankAccount->number,
            'transfer_country' => $bankAccount->country,
            'transfer_note' => $bankAccount->note,
        ]);
    }

    /**
     * Create Gateway receive
     *
     * @param  Money  $amount
     * @param  Collection  $data
     * @return PaymentTransaction
     */
    public function receiveViaGateway(Money $amount, Collection $data): PaymentTransaction
    {
        $value = $this->validateAmount($amount);
        $gateway = $this->prepareGatewayData($data);

        return $this->transactions()->create([
            'id' => $gateway->get('uuid'),
            'type' => 'receive',
            'status' => 'pending-gateway',
            'value' => $value,
            'description' => $gateway->get('description'),
            'gateway_ref' => $gateway->get('ref'),
            'gateway_name' => $gateway->get('name'),
            'gateway_url' => $gateway->get('url'),
        ]);
    }

    /**
     * Validate Gateway data
     *
     * @param  Collection  $data
     * @return Collection
     */
    protected function prepareGatewayData(Collection $data): Collection
    {
        return tap($data, function (Collection $data) {
            Validator::make($data->all(), [
                'uuid' => 'nullable|uuid',
                'ref' => 'required|string',
                'name' => 'required|string',
                'url' => 'required|url',
            ])->validate();

            $gateway = app('multipay')->gateway($data->get('name'));

            $data->put('description', trans('payment.gateway_description', [
                'reference' => $data->get('ref'),
                'gateway' => $gateway->getName(),
            ]));
        });
    }

    /**
     * Validate amount
     *
     * @param  Money  $amount
     * @return Money
     */
    protected function validateAmount(Money $amount): Money
    {
        return tap($amount, function (Money $amount) {
            if ($this->currency != $amount->getCurrency()->getCurrency()) {
                throw new UnexpectedValueException('Unexpected currency.');
            }
        });
    }

    /**
     * Get daily chart data
     *
     * @param  int|null  $month
     * @param  int|null  $year
     * @return Collection
     */
    public function getDailyChartData(int $month = null, int $year = null): Collection
    {
        $starts = Carbon::createFromDate($year ?: now()->year, $month ?: now()->month, 1);
        $ends = $starts->clone()->endOfMonth();

        $received = $this->totalReceivedQuery()
            ->selectRaw('sum(value) as total')
            ->selectRaw('day(created_at) as day')
            ->whereDate('created_at', '>=', $starts)
            ->whereDate('created_at', '<=', $ends)
            ->groupBy('day')->get()
            ->pluck('total', 'day');

        $sent = $this->totalSentQuery()
            ->selectRaw('day(created_at) as day')
            ->selectRaw('sum(value) as total')
            ->whereDate('created_at', '>=', $starts)
            ->whereDate('created_at', '<=', $ends)
            ->groupBy('day')->get()
            ->pluck('total', 'day');

        return tap(new Collection(), function ($collection) use ($starts, $received, $sent) {
            for ($day = 1; $day <= $starts->daysInMonth; $day++) {
                $totalSent = $this->supportedCurrency->castMoney($sent->get($day, 0));
                $totalReceived = $this->supportedCurrency->castMoney($received->get($day, 0));
                $current = $starts->clone()->day($day);

                $data['date'] = $current->toDateString();
                $data['formatted_total_sent'] = $totalSent->format();
                $data['total_sent'] = $totalSent->getValue();
                $data['formatted_total_received'] = $totalReceived->format();
                $data['total_received'] = $totalReceived->getValue();
                $collection->push($data);
            }
        });
    }
}
