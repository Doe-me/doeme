<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'images',
        'condition',
        'location',
        'latitude',
        'longitude',
        'status',
        'donated_at',
        'donated_to_user_id',
    ];

    protected $casts = [
        'images' => 'array',
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
     * Scope para itens disponíveis
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope para busca por localização (bounding box SQL + Haversine em PHP).
     *
     * Não usa funções matemáticas do BD (ST_Distance_Sphere, acos, etc.) para
     * ser compatível com SQLite em dev e PostgreSQL em produção. O filtro de
     * raio exato e a ordenação por distância são feitos pela camada PHP após
     * buscar os candidatos da bounding box.
     */
    public function scopeNearLocation(Builder $query, float $latitude, float $longitude, float $radius = 10): Builder
    {
        // 1° de latitude ≈ 111 km; 1° de longitude varia com o cosseno da latitude
        $latDelta = $radius / 111.0;
        $lonDelta = $radius / (111.0 * cos(deg2rad($latitude)));

        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lonDelta, $longitude + $lonDelta]);
    }
}
