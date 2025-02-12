<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CrimeResource;
use App\Models\Crime;
use App\Models\District;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Livewire\WithPagination;

class HomePage extends Dashboard
{
    use WithPagination;
    use HasFiltersAction;

    protected static ?string $navigationLabel = 'Home';

    protected static string $view = 'filament.pages.home-page';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('report-a-crime')
                ->url(fn () => CrimeResource::getUrl('create'))
                ->icon('heroicon-o-plus-circle'),
            FilterAction::make()
                ->form([
                    CheckboxList::make('district_id')
                        ->options(District::all()->pluck('name', 'id'))
                        ->searchable(),
                ]),
        ];
    }

    protected function getViewData(): array
    {
        $districtId = $this->filters['district_id'] ?? null;

        return [
            'crimes' => Crime::query()
                ->when($districtId, function ($query) use ($districtId) {
                    $query->where('district_id', $districtId);
                })
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
