<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use HasFactory;


    protected $fillable = [
        'review',
        'rating',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder
    {

        return $query->where('title', 'like', '%' . $title . '%');
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder
    {

        return $query->withCount(['reviews' => function (Builder $q) use ($from, $to) {
        
            if ($from && !$to) {
                $q->where('created_at', '>=', $from);
            }
            elseif ($to && !$from) {
                $q->where('created_at', '<=', $to);
            }
            elseif ($from && $to) {
                $q->whereBetween('created_at', [$from, $to]);
            }
        
        }])->orderBy('reviews_count', 'desc');
    }


    public function scopeMinReviews(Builder $query, int $minReviews): Builder
    {

        return $query->having('reviews_count', '>=', $minReviews);
    }

    public function scopeHighestRated(Builder $query): Builder
    {

        return $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopePopularLastMonth(Builder $query): Builder
    {
        return $query->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLast6Months(Builder $query): Builder
    {
        return $query->popular(now()->subMonths(6), now())
            ->highestRated(now()->subMonths(6), now())
            ->minReviews(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query): Builder
    {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopeHighestRatedLast6Months(Builder $query): Builder
    {
        return $query->highestRated(now()->subMonths(6), now())
            ->popular(now()->subMonths(6), now())
            ->minReviews(5);
    }
}
