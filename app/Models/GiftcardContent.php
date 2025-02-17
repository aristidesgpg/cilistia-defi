<?php

namespace App\Models;

use App\Models\Support\Lock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftcardContent extends Model
{
    use HasFactory, Lock;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'code' => 'encrypted',
        'bought_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['serial', 'code'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['giftcard', 'buyer'];

    /**
     * The giftcard
     *
     * @return BelongsTo
     */
    public function giftcard(): BelongsTo
    {
        return $this->belongsTo(Giftcard::class, 'giftcard_id', 'id');
    }

    /**
     * The buyer
     *
     * @return BelongsTo
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    /**
     * @param  User|null  $user
     * @return bool
     */
    public function canViewCode(?User $user): bool
    {
        return $user?->is($this->buyer) || $user?->can('manage_giftcards');
    }
}
