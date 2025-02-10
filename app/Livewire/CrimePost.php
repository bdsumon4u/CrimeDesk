<?php

namespace App\Livewire;

use App\Models\Crime;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class CrimePost extends Component
{
    public Crime $crime;

    /**
     * Refresh the component.
     */
    #[On('crime.updated')]
    #[On('crime.created')]
    public function refresh(): void
    {
        $this->crime->loadCount(['upvotes', 'downvotes', 'comments']);
    }

    /**
     * Get the placeholder for the component.
     */
    public function placeholder(): View
    {
        return view('livewire.crime-placeholder'); // @codeCoverageIgnore
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.crime-post');
    }
}
