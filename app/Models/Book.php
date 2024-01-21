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
        
        }])->orderBy('reviews_count', 'desc')->limit(10);
    }


    public function scopeMinReviews(Builder $query, int $minReviews): Builder
    {

        return $query->having('reviews_count', '>=', $minReviews);
    }

    public function scopeHighestRated(Builder $query): Builder
    {

        return $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
    }
}
