<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Get path for logo
     *
     * @return string
     */
    public function path(): string
    {
        return "banks/{$this->id}";
    }

    /**
     * Get logo url
     *
     * @param $value
     * @return string|null
     */
    protected function getLogoAttribute($value): ?string
    {
        return $value ? url($value) : null;
    }

    /**
     * Bank accounts relation
     *
     * @return HasMany
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'bank_id', 'id');
    }

    /**
     * Related operating countries
     *
     * @return BelongsToMany
     */
    public function operatingCountries(): BelongsToMany
    {
        return $this->belongsToMany(OperatingCountry::class, 'operating_country_bank', 'bank_id', 'operating_country_code')->withTimestamps();
    }

    /**
     * Filter by country.
     *
     * @param  Builder  $query
     * @param  string  $code
     * @return Builder
     */
    public function scopeCountry(Builder $query, string $code): Builder
    {
        return $query->whereHas('operatingCountries', function (Builder $query) use ($code) {
            $query->where('code', strtoupper($code));
        });
    }
}
