<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequiredDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get user document
     *
     * @param  User  $user
     * @return UserDocument|null
     */
    public function getDocument(User $user): ?UserDocument
    {
        return $user->documents()
            ->where('required_document_id', $this->id)
            ->latest()->first();
    }

    /**
     * Related documents
     *
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class, 'required_document_id', 'id');
    }

    /**
     * Filter by enabled requirement.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
