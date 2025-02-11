<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Filament\Facades\Filament;

trait NeedsVerifiedPhone
{
    /**
     * Determine if the user does not have a verified phone number.
     */
    public function doesNotHaveVerifiedPhone(): bool
    {
        if (optional(Filament::auth()->user())->hasVerifiedPhone()) {
            return false;
        }

        session()->flash('flash-message', 'You must verify your phone number before you can continue.');

        $this->redirectRoute('verification.phone', navigate: true);

        return true;
    }
}
