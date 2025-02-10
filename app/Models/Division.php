<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function crimes(): HasMany
    {
        return $this->hasMany(Crime::class);
    }
}
