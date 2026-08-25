<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitReason extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "description",
        "proof_url",
        "permit_category_id"
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PermitCategory::class, 'permit_category_id');
    }
}
