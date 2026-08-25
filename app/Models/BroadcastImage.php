<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'broadcast_id',
        'image',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Broadcast.
     */
    public function broadcast()
    {
        return $this->belongsTo(Broadcast::class);
    }
}