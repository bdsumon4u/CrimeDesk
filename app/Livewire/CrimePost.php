<?php

namespace App\Livewire;

use App\Livewire\Concerns\NeedsVerifiedPhone;
use App\Models\Crime;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class CrimePost extends Component
{
    use NeedsVerifiedPhone;

    public Crime $crime;

    /**
     * Refresh the component.
     */
    #[On('crime.updated')]
    #[On('crime.created')]
    #[On('comment-created')]
    public function refresh(): void
    {
        $this->crime->loadCount(['upvotes', 'downvotes', 'comments']);
    }

    /**
     * Like the crime.
     */
    // #[Renderless]
    public function like(): void
    {
        if ($this->doesNotHaveVerifiedPhone()) {
            return;
        }

        $this->crime->like();
        // $this->dispatch('crime.updated');
        $this->refresh();
    }

    /**
     * Dislike the crime.
     */
    // #[Renderless]
    public function dislike(): void
    {
        if ($this->doesNotHaveVerifiedPhone()) {
            return;
        }

        $this->crime->dislike();
        // $this->dispatch('crime.updated');
        $this->refresh();
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
