<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DonationImages extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'donation_item_id'];

    /**
     * A URL pública é sempre derivada do path armazenado (disk "public"),
     * então expomos ela na serialização para o frontend consumir direto.
     */
    protected $appends = ['url'];

    public function donationItem()
    {
        return $this->belongsTo(DonationItem::class);
    }

    /**
     * URL pública da imagem a partir do path no disco "public".
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
