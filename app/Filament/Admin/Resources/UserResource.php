<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Pages\ProfilePage;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (User $record) => ProfilePage::getUrl(['user' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->icon(fn (User $record) => $record->hasVerifiedEmail() ? 'heroicon-o-check-badge' : null)
                    ->iconColor(fn (User $record) => $record->hasVerifiedEmail() ? Color::Green : null),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->sortable()
                    ->icon(fn (User $record) => $record->hasVerifiedPhone() ? 'heroicon-o-check-badge' : null)
                    ->iconColor(fn (User $record) => $record->hasVerifiedPhone() ? Color::Green : null),
                Tables\Columns\TextColumn::make('crimes_count')
                    ->label(__('Reports'))
                    ->counts('crimes')
                    ->sortable()
                    ->badge()
                    ->alignCenter(),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label(__('Email Verification'))
                    ->placeholder(__('Any'))
                    ->trueLabel(__('Verified'))
                    ->falseLabel(__('Unverified'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    )
                    ->native(false),
                TernaryFilter::make('phone_verified_at')
                    ->label(__('Phone Verification'))
                    ->placeholder(__('Any'))
                    ->trueLabel(__('Verified'))
                    ->falseLabel(__('Unverified'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('phone_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('phone_verified_at'),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    )
                    ->native(false),
                TernaryFilter::make('banned_at')
                    ->label(__('Banned'))
                    ->placeholder(__('Any'))
                    ->trueLabel(__('Banned'))
                    ->falseLabel(__('Not Banned'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('banned_at'),
                        false: fn (Builder $query) => $query->whereNull('banned_at'),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    )
                    ->native(false),
            ])
            ->actions([
                // Tables\Actions\EditAction::make()
                //     ->slideOver()
                //     ->modalWidth('md'),
                Tables\Actions\Action::make('ban')
                    ->label(fn (User $record) => $record->isBanned() ? __('Unban') : __('Ban'))
                    ->icon(fn (User $record) => $record->isBanned() ? 'heroicon-o-check-badge' : 'heroicon-o-x-circle')
                    ->color(fn (User $record) => $record->isBanned() ? Color::Green : Color::Red)
                    ->action(function (User $record, $action) {
                        if ($record->isBanned()) {
                            $record->unban();
                            Notification::make()
                                ->title(__('User unbanned successfully'))
                                ->send();
                        } else {
                            $record->ban();
                            Notification::make()
                                ->title(__('User banned successfully'))
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('crimes');
    }
}
