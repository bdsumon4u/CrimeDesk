<?php

namespace App\Models\Concerns;

use Filament\Facades\Filament;

trait UserCanComment
{
    // Create comment
    public function commentCanCreate(): bool
    {
        return false;
        if (! optional(Filament::auth()->user())->hasVerifiedPhone()) {
            abort(403, 'Please verify your phone number.');
        }

        return true;
    }

    // Edit comment
    public function commentCanEdit(): bool
    {
        return false;
        if (! optional(Filament::auth()->user())->hasVerifiedPhone()) {
            abort(403, 'Please verify your phone number.');
        }

        return true;
    }

    // Delete comment
    public function commentCanDelete(): bool
    {
        return false;
        if (! optional(Filament::auth()->user())->hasVerifiedPhone()) {
            abort(403, 'Please verify your phone number.');
        }

        return true;
    }
}
