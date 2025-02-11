<?php

namespace App\Livewire;

use App\Livewire\Concerns\NeedsVerifiedPhone;
use App\Models\Crime;
use Livewire\Attributes\On;
use Livewire\Component;

class ReactCounter extends Component
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

    public function render()
    {
        return view('livewire.react-counter');
    }
}
