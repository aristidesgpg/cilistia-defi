<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address',
        'unit',
        'city',
        'postcode',
        'state',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_complete',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['address', 'unit', 'city', 'postcode', 'state'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'address' => 'encrypted',
        'unit' => 'encrypted',
        'city' => 'encrypted',
        'postcode' => 'encrypted',
        'state' => 'encrypted',
    ];

    /**
     * Check if profile is complete
     *
     * @return bool
     */
    protected function getIsCompleteAttribute(): bool
    {
        return !empty($this->address)
            && !empty($this->unit)
            && !empty($this->city)
            && !empty($this->postcode)
            && !empty($this->state);
    }

    /**
     * Related user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
