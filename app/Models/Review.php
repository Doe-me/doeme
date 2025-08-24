<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_item_id',
        'reviewer_id',
        'reviewed_user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Relacionamento com o item de doação
     */
    public function donationItem()
    {
        return $this->belongsTo(DonationItem::class);
    }

    /**
     * Relacionamento com o usuário que fez a avaliação
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Relacionamento com o usuário avaliado
     */
    public function reviewedUser()
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }

    /**
     * Scope para avaliações por rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope para avaliações de um usuário específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('reviewed_user_id', $userId);
    }
}

