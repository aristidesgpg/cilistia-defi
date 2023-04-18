<?php

namespace App\Models;

use App\CoinAdapters\Exceptions\AdapterException;
use App\CoinAdapters\Resources\Address as AddressResource;
use App\Helpers\CoinFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Throwable;

class WalletAddress extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'address';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['label', 'resource'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_received_price',
        'formatted_total_received_price',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'consolidated' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address',
        'label',
        'consolidated',
        'resource',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['walletAccount'];

    /**
     * Consolidate address and set its state.
     *
     * @param  bool  $shouldRetry
     * @return void
     *
     * @throws Throwable
     */
    public function consolidate(bool $shouldRetry = false): void
    {
        if ($this->wallet->consolidates) {
            try {
                $this->wallet->coin->adapter->consolidate(
                    $this->wallet->resource,
                    $this->address,
                    $this->wallet->passphrase
                );

                $this->update(['consolidated' => true]);
            } catch (AdapterException $exception) {
                $this->update(['consolidated' => $exception->getCode() === 409 || !$shouldRetry]);
                throw $exception;
            } catch (Throwable $exception) {
                $this->update(['consolidated' => !$shouldRetry]);
                throw $exception;
            }
        }
    }

    /**
     * @return CoinFormatter
     */
    public function getTotalReceivedObject(): CoinFormatter
    {
        return $this->total_received;
    }

    /**
     * Get total received
     *
     * @return Attribute
     */
    protected function totalReceived(): Attribute
    {
        return Attribute::get(function (): CoinFormatter {
            $totalReceived = $this->transferRecords()
                ->whereColumn('confirmations', '>=', 'required_confirmations')
                ->where('type', 'receive')->sum('value');

            return $this->walletAccount->wallet->castCoin($totalReceived);
        });
    }

    /**
     * @return float|string
     */
    protected function getTotalReceivedPriceAttribute(): float|string
    {
        return $this->getTotalReceivedObject()->getPrice($this->walletAccount->user->currency);
    }

    /**
     * @return string
     */
    protected function getFormattedTotalReceivedPriceAttribute(): string
    {
        return $this->getTotalReceivedObject()->getFormattedPrice($this->walletAccount->user->currency);
    }

    /**
     * Get total received from date
     *
     * @param  Carbon  $startDate
     * @return CoinFormatter
     */
    public function getTotalReceivedFrom(Carbon $startDate): CoinFormatter
    {
        $totalReceived = $this->transferRecords()
            ->where('created_at', '>=', $startDate->toDateTimeString())
            ->whereColumn('confirmations', '>=', 'required_confirmations')
            ->where('type', 'receive')->sum('value');

        return $this->walletAccount->wallet->castCoin($totalReceived);
    }

    /**
     * Get unconfirmed total received from date
     *
     * @param  Carbon  $startDate
     * @return CoinFormatter
     */
    public function getUnconfirmedTotalReceivedFrom(Carbon $startDate): CoinFormatter
    {
        $totalReceived = $this->transferRecords()
            ->where('created_at', '>=', $startDate->toDateTimeString())
            ->whereColumn('confirmations', '<', 'required_confirmations')
            ->where('type', 'receive')->sum('value');

        return $this->walletAccount->wallet->castCoin($totalReceived);
    }

    /**
     * Get total received including confirmed and unconfirmed
     *
     * @param  Carbon  $startDate
     * @return CoinFormatter
     */
    public function getAbsoluteTotalReceivedFrom(Carbon $startDate): CoinFormatter
    {
        $totalReceived = $this->transferRecords()
            ->where('created_at', '>=', $startDate->toDateTimeString())
            ->where('type', 'receive')->sum('value');

        return $this->walletAccount->wallet->castCoin($totalReceived);
    }

    /**
     * Get adapter resource
     *
     * @return Attribute
     */
    protected function resource(): Attribute
    {
        return Attribute::make(
            get: function ($value): AddressResource {
                if (Str::isJson($value)) {
                    return new AddressResource(json_decode($value, true));
                } else {
                    return unserialize($value);
                }
            },
            set: fn (AddressResource $resource) => $resource->toJson()
        );
    }

    /**
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'wallet_account_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function transferRecords(): HasMany
    {
        $relation = $this->hasMany(TransferRecord::class, 'address', 'address');

        if ($this->wallet_account_id) {
            return $relation->where('wallet_account_id', $this->wallet_account_id);
        } else {
            return $relation;
        }
    }

    /**
     * Related commerce transactions
     *
     * @return HasMany
     */
    public function commerceTransactions(): HasMany
    {
        $relation = $this->hasMany(CommerceTransaction::class, 'address', 'address')->latest();

        if ($this->wallet_account_id) {
            return $relation->where('wallet_account_id', $this->wallet_account_id);
        } else {
            return $relation;
        }
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeUnconsolidated(Builder $query): Builder
    {
        return $query->where('consolidated', false);
    }
}
