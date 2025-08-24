<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_item_id',
        'donor_id',
        'interested_user_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Relacionamento com o item de doação
     */
    public function donationItem()
    {
        return $this->belongsTo(DonationItem::class);
    }

    /**
     * Relacionamento com o doador
     */
    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    /**
     * Relacionamento com o usuário interessado
     */
    public function interestedUser()
    {
        return $this->belongsTo(User::class, 'interested_user_id');
    }

    /**
     * Relacionamento com as mensagens
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Relacionamento com a última mensagem
     */
    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }

    /**
     * Verifica se o usuário participa do chat
     */
    public function hasUser($userId)
    {
        return $this->donor_id == $userId || $this->interested_user_id == $userId;
    }
}

