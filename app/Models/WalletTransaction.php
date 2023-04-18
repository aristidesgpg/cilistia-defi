<?php

namespace App\Models;

use App\CoinAdapters\Resources\Transaction as TransactionResource;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use UnexpectedValueException;

class WalletTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['resource'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'datetime',
        'confirmations' => 'integer',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'wallet_id', 'created_at', 'updated_at'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::updating(function (self $record) {
            if ($record->getOriginal('confirmations') > $record->confirmations) {
                throw new UnexpectedValueException('You cannot decrease confirmation.');
            }

            if ($record->isDirty('hash', 'type', 'value')) {
                throw new UnexpectedValueException('You cannot change property.');
            }
        });
    }

    /**
     * Adapter's transaction resource
     *
     * @return Attribute
     */
    protected function resource(): Attribute
    {
        return Attribute::make(
            get: function ($value): TransactionResource {
                if (Str::isJson($value)) {
                    return new TransactionResource(json_decode($value, true));
                } else {
                    return unserialize($value);
                }
            },
            set: fn (TransactionResource $resource) => $resource->toJson()
        );
    }

    /**
     * Wallet relation
     *
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    /**
     * Transfer record relation
     *
     * @return HasMany
     */
    public function transferRecords(): HasMany
    {
        return $this->hasMany(TransferRecord::class, 'wallet_transaction_id', 'id');
    }
}
