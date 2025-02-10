<?php

namespace App\Filament\Resources\CrimeResource\Pages;

use App\Filament\Resources\CrimeResource;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCrime extends CreateRecord
{
    protected static string $resource = CrimeResource::class;

    protected static ?string $title = 'Report Crime';

    protected static ?string $breadcrumb = 'Report';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Filament::auth()->id();

        return $data;
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('Report'))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label(__('Report and report another'))
            ->action('createAnother')
            ->keyBindings(['mod+shift+s'])
            ->color('gray');
    }
}
