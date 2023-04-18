<?php

namespace App\Models\Support;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRatings
{
    /**
     * Ratings relation
     *
     * @return MorphMany
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Average rating
     *
     * @return mixed
     */
    public function averageRating(): mixed
    {
        return $this->ratings()->avg('value');
    }

    /**
     * Sum rating
     *
     * @return mixed
     */
    public function sumRating(): mixed
    {
        return $this->ratings()->sum('value');
    }

    /**
     * Total rating
     *
     * @return int
     */
    public function totalRating(): int
    {
        return $this->ratings()->count();
    }

    /**
     * Total users rated
     *
     * @return int
     */
    public function usersRated(): int
    {
        return $this->ratings()->groupBy('user_id')->pluck('user_id')->count();
    }

    /**
     * Average rating
     *
     * @return float
     */
    protected function getAverageRatingAttribute(): float
    {
        return (float) $this->averageRating();
    }

    /**
     * Sum rating
     *
     * @return int
     */
    protected function getSumRatingAttribute(): int
    {
        return (int) $this->sumRating();
    }

    /**
     * Total rating
     *
     * @return int
     */
    protected function getTotalRatingAttribute(): int
    {
        return $this->totalRating();
    }
}
