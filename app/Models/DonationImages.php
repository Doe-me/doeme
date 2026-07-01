<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationImages extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'donation_item_id'];

    public function donationItem()
    {
        return $this->belongsTo(DonationItem::class);
    }
}
