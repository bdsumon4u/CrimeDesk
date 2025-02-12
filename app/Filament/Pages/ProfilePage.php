<?php

namespace App\Filament\Pages;

use App\Models\Crime;
use App\Models\User;
use Exception;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Form $form
 */
class ProfilePage extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = '{user}/profile';

    /**
     * @var view-string
     */
    protected static string $view = 'filament.pages.profile-page';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static bool $isDiscovered = true;

    // Add this property to store the user
    public ?Model $record = null;

    public static function getLabel(): string
    {
        return __('filament-panels::pages/auth/edit-profile.label');
    }

    public function mount(User $user): void
    {
        $this->record = $user;
    }

    // Update getUser to use the record instead of authenticated user
    public function getUser(): Model
    {
        if (! $this->record instanceof Model) {
            throw new Exception('The user object must be an Eloquent model.');
        }

        return $this->record;
    }

    protected function getViewData(): array
    {
        return [
            'crimes' => Crime::query()
                ->where('user_id', $this->record->id)
                ->with(['user', 'media', 'district', 'division', 'userReact', 'comments'])
                ->withCount(['upvotes', 'downvotes'])
                ->orderByDesc('created_at')
                ->paginate(10),
        ];
    }
}
