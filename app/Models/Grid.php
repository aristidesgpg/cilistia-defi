<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grid extends Model
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
        'dimensions' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['title', 'page_title'];

    /**
     * The title attribute for this template
     *
     * @return string
     */
    protected function getTitleAttribute(): string
    {
        return trans("grid.{$this->name}");
    }

    /**
     * Get page title
     *
     * @return string|null
     */
    protected function getPageTitleAttribute(): ?string
    {
        return match ($this->page) {
            'admin.home' => trans('grid.admin_home'),
            'index.home' => trans('grid.index_home'),
            default => null,
        };
    }

    /**
     * Visibility scope
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Visibility scope
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWithOrder(Builder $query): Builder
    {
        return $query->orderBy('order');
    }
}
