<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'facebook_id',
        'avatar',
        'phone',
        'location',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relacionamento com itens de doação
     */
    public function donationItems()
    {
        return $this->hasMany(DonationItem::class);
    }

    /**
     * Relacionamento com chats como doador
     */
    public function donorChats()
    {
        return $this->hasMany(Chat::class, 'donor_id');
    }

    /**
     * Relacionamento com chats como interessado
     */
    public function interestedChats()
    {
        return $this->hasMany(Chat::class, 'interested_user_id');
    }

    /**
     * Relacionamento com mensagens de chat
     */
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Relacionamento com avaliações feitas
     */
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Relacionamento com avaliações recebidas
     */
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
    }

    /**
     * Relacionamento com itens recebidos
     */
    public function receivedItems()
    {
        return $this->hasMany(DonationItem::class, 'donated_to_user_id');
    }

    /**
     * Calcula a média de avaliações do usuário
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviewsReceived()->avg('rating') ?? 0;
    }

    /**
     * Conta o total de avaliações recebidas
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviewsReceived()->count();
    }
}
