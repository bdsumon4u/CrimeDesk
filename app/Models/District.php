<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function crimes(): HasMany
    {
        return $this->hasMany(Crime::class);
    }
}
