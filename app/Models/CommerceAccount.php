<?php

namespace App\Models;

use App\Casts\PurifyHtml;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CommerceAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['name', 'email', 'website', 'phone', 'about'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['user'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'about' => PurifyHtml::class,
    ];

    /**
     * Get logo path
     *
     * @return string
     */
    public function path(): string
    {
        return "commerce-accounts/{$this->id}";
    }

    /**
     * Get logo url
     *
     * @param $value
     * @return string|null
     */
    public function getLogoAttribute($value): ?string
    {
        return $value ? url($value) : null;
    }

    /**
     * Get transaction aggregate
     *
     * @param  Carbon|null  $from
     * @param  Carbon|null  $to
     * @return Collection
     */
    public function getTransactionAggregate(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $query = $this->transactions();

        if ($from instanceof Carbon) {
            $query->where('created_at', '>=', $from->toDateTimeString());
        }

        if ($to instanceof Carbon) {
            $query->where('created_at', '<=', $to->toDateTimeString());
        }

        $query->selectRaw('count(*) as total');
        $query->selectRaw('sum(value) as total_value');
        $query->selectRaw('wallet_account_id, status');
        $query->groupBy('wallet_account_id', 'status');

        return $query->get()->map(function ($result) {
            $walletAccount = $this->getWalletAccount($result->wallet_account_id);
            $totalValue = $walletAccount->wallet->castCoin($result->total_value);
            $totalPrice = $totalValue->getPriceAsMoney($this->user->currency);

            return [
                'status' => $result->status,
                'coin' => $walletAccount->wallet->coin->identifier,
                'total_value' => $totalValue->getValue(),
                'total_price' => $totalPrice->getValue(),
                'formatted_total_price' => $totalPrice->format(),
                'total' => $result->total,
            ];
        });
    }

    /**
     * Aggregate transaction status
     *
     * @param  string  $status
     * @param  Carbon|null  $from
     * @param  Carbon|null  $to
     * @return Collection
     */
    public function getTransactionStatusAggregate(string $status, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $query = $this->transactions()->whereStatus($status);

        if ($from instanceof Carbon) {
            $query->where('created_at', '>=', $from->toDateTimeString());
        }

        if ($to instanceof Carbon) {
            $query->where('created_at', '<=', $to->toDateTimeString());
        }

        $query->selectRaw('count(*) as total');
        $query->selectRaw('sum(value) as total_value');
        $query->selectRaw('wallet_account_id');
        $query->groupBy('wallet_account_id');

        return $query->get()->map(function ($result) {
            $walletAccount = $this->getWalletAccount($result->wallet_account_id);
            $totalValue = $walletAccount->wallet->castCoin($result->total_value);
            $totalPrice = $totalValue->getPriceAsMoney($this->user->currency);

            return [
                'coin' => $walletAccount->wallet->coin->identifier,
                'total_value' => $totalValue->getValue(),
                'total_price' => $totalPrice->getValue(),
                'formatted_total_price' => $totalPrice->format(),
                'total' => $result->total,
            ];
        });
    }

    /**
     * Get total customers
     *
     * @param  Carbon|null  $from
     * @param  Carbon|null  $to
     * @return int
     */
    public function getCustomerCount(?Carbon $from = null, ?Carbon $to = null): int
    {
        $query = $this->customers();

        if ($from instanceof Carbon) {
            $query->where('created_at', '>=', $from->toDateTimeString());
        }

        if ($to instanceof Carbon) {
            $query->where('created_at', '<=', $to->toDateTimeString());
        }

        return $query->count();
    }

    /**
     * Find related wallet account
     *
     * @param  int  $id
     * @return WalletAccount
     */
    public function getWalletAccount(int $id): WalletAccount
    {
        return $this->user->walletAccounts()->findOrFail($id);
    }

    /**
     * Business owner
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Related customers
     *
     * @return HasMany
     */
    public function customers(): HasMany
    {
        return $this->hasMany(CommerceCustomer::class, 'commerce_account_id', 'id');
    }

    /**
     * Related payments
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(CommercePayment::class, 'commerce_account_id', 'id');
    }

    /**
     * Related Transaction
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CommerceTransaction::class, 'commerce_account_id', 'id');
    }
}
