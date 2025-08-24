<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'condition',
        'address',
        'latitude',
        'longitude',
        'status',
        'donated_at',
        'donated_to_user_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'donated_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário doador
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com a categoria
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relacionamento com o usuário que recebeu a doação
     */
    public function donatedToUser()
    {
        return $this->belongsTo(User::class, 'donated_to_user_id');
    }

    /**
     * Relacionamento com chats
     */
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * Relacionamento com avaliações
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relacionamento com imagens
     */
    public function donationImages()
    {
        return $this->hasMany(DonationImages::class, 'donation_item_id');
    }

    /**
     * Scope para itens disponíveis
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope para busca por localização
     */
    public function scopeNearLocation($query, $latitude, $longitude, $radius = 10)
    {
        return $query->whereRaw(
            "ST_Distance_Sphere(
                POINT(longitude, latitude),
                POINT(?, ?)
            ) <= ?",
            [$longitude, $latitude, $radius * 1000]
        );
    }
}

