<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftcardBrand extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Related giftcards
     *
     * @return HasMany
     */
    public function giftcards(): HasMany
    {
        return $this->hasMany(Giftcard::class, 'brand_id', 'id');
    }
}
