<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CrimeResource;
use App\Models\Crime;
use Filament\Actions\Action;
use Filament\Pages\Dashboard;
use Livewire\WithPagination;

class HomePage extends Dashboard
{
    use WithPagination;

    protected static ?string $navigationLabel = 'Home';

    protected static string $view = 'filament.pages.home-page';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('report-a-crime')
                ->url(fn () => CrimeResource::getUrl('create'))
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'crimes' => Crime::query()
                ->with(['user', 'media', 'district', 'division', 'userReact', 'comments'])
                ->withCount(['upvotes', 'downvotes'])
                ->orderByDesc('created_at')
                ->paginate(10),
        ];
    }

    public function getColumns(): int
    {
        return 1;
    }
}
