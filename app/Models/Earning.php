<?php

namespace App\Models;

use Akaunting\Money\Currency;
use App\Casts\MoneyCast;
use App\Helpers\CoinFormatter;
use App\Models\Support\CurrencyAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Earning extends Model implements CurrencyAttribute
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['value', 'description'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => MoneyCast::class . ':false',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'precision' => 2,
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(MoneyCast::assert('value'));

        static::saving(function (Earning $earning) {
            $earning->precision = $earning->value->getCurrency()->getPrecision();
        });
    }

    /**
     * Get earning instance
     *
     * @param  Model  $transaction
     * @return Earning
     */
    protected static function getTransactionInstance(Model $transaction): Earning
    {
        $query = Earning::whereMorphedTo('transaction', $transaction);

        return tap($query->firstOrNew(), function (Earning $earning) use ($transaction) {
            $earning->transaction()->associate($transaction);
        });
    }

    /**
     * Add earning associated with a wallet transfer
     *
     * @param  TransferRecord  $transaction
     * @return Earning
     */
    public static function saveWalletTransaction(TransferRecord $transaction): Earning
    {
        $earning = static::getTransactionInstance($transaction);

        $earning->value = $transaction->value->getPriceAsMoney('USD', $transaction->dollar_price);
        $earning->description = $transaction->description;

        $earning->receiver()->associate($transaction->walletAccount->user);

        return tap($earning)->save();
    }

    /**
     * Add coin value to earnings
     *
     * @param  CoinFormatter  $value
     * @param  string  $description
     * @param  User|null  $receiver
     * @return Earning
     */
    public static function saveCoin(CoinFormatter $value, string $description, User $receiver = null): Earning
    {
        $earning = new Earning();

        $earning->value = $value->getPriceAsMoney();
        $earning->description = $description;

        $earning->receiver()->associate($receiver);

        return tap($earning)->save();
    }

    /**
     * Get formatted value
     *
     * @return string
     */
    protected function getFormattedValueAttribute(): string
    {
        return $this->value->format();
    }

    /**
     * Related PaymentTransaction, TransferRecord
     *
     * @return MorphTo
     */
    public function transaction(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The operator who received the earning
     *
     * @return BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return currency('USD', $this->precision);
    }
}
