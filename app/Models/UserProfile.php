<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'dob',
        'bio',
        'picture',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['last_name', 'first_name', 'dob', 'bio', 'picture'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_complete',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'dob' => 'datetime',
    ];

    /**
     * Get full name
     *
     * @return string
     */
    protected function getFullNameAttribute(): string
    {
        return !$this->is_complete ? '' : "$this->first_name $this->last_name";
    }

    /**
     * Get picture url
     *
     * @param $value
     * @return string|null
     */
    protected function getPictureAttribute($value): ?string
    {
        return $value ? url($value) : null;
    }

    /**
     * Check if profile is complete
     *
     * @return bool
     */
    protected function getIsCompleteAttribute(): bool
    {
        return !empty($this->last_name)
            && !empty($this->first_name);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
