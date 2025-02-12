<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrimeResource\Pages;
use App\Models\Crime;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;

class CrimeResource extends Resource
{
    private static $map = [];

    protected static ?string $model = Crime::class;

    protected static ?string $navigationLabel = 'My Reports';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('evidence')
                    ->label('Evidence')
                    ->placeholder('Upload evidence (Images/Video)')
                    ->multiple()
                    ->conversion('webp')
                    ->optimize('webp')
                    ->resize(30)
                    ->minFiles(1)
                    ->acceptedFileTypes(['image/*', 'video/mp4', 'video/quicktime'])
                    ->maxSize(50 * 1024) // 50MB limit
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (string $operation, $state, callable $get, callable $set) {
                        if (empty($state) || $operation !== 'create') {
                            return;
                        }

                        $set('description', null);
                        $image_urls = [];
                        foreach ($state as $file) {
                            if (str_starts_with($file->getMimeType(), 'image/')) {
                                $image_urls[] = $file->temporaryUrl();
                            }
                        }
                        if ($image_urls) {
                            $response = Http::post('https://crime-image-caption-generator-api.onrender.com/generate_caption', [
                                'image_urls' => $image_urls,
                                'language' => 'Bangla',
                            ]);
                            $set('description', $response->json('english_summary_caption'));
                        }
                        // foreach ($state as $file) {
                        //     if (is_string($file)) {
                        //         continue;
                        //     }
                        //     if (str_starts_with($file->getMimeType(), 'image/')) {
                        //         // Use a free AI service to generate description
                        //         $description = static::generateDescriptionFromImage($file->temporaryUrl());

                        //         if (filled($get('description'))) {
                        //             $description = $get('description').'<br><br>'.$description;
                        //         } else {
                        //             $description = $description;
                        //         }
                        //         $set('description', $description);
                        //     }
                        // }

                        if (blank($get('description'))) {
                            $set('description', 'No images uploaded. Please provide a manual description.');
                        }
                    }),
                Group::make([
                    TextInput::make('title')
                        ->label('Title')
                        ->placeholder('Enter the title of the crime')
                        ->required(),
                    Group::make([
                        Select::make('division_id')
                            ->label('Division')
                            ->relationship('division', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn (callable $set) => $set('district_id', null)),
                        Select::make('district_id')
                            ->label('District')
                            ->relationship('district', 'name', function (Builder $query, $get) {
                                $query->where('division_id', $get('division_id'));
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),
                    DateTimePicker::make('happened_at')
                        ->placeholder('Select the time of the crime')
                        ->label('Crime Time')
                        ->required()
                        ->native(false)
                        ->rules('before_or_equal:now'),
                    RichEditor::make('description')
                        ->label('Description')
                        ->placeholder('Enter the description of the crime')
                        ->required()
                        ->visible(fn (callable $get) => filled($get('evidence'))),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('division.name')
                    ->searchable(),
                TextColumn::make('district.name')
                    ->searchable(),
                TextColumn::make('happened_at')
                    ->dateTime('d-M-Y')
                    ->label('Crime Time')
                    ->timeTooltip(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCrimes::route('/'),
            'create' => Pages\CreateCrime::route('/create'),
            'edit' => Pages\EditCrime::route('/{record}/edit'),
        ];
    }

    private static function generateDescriptionFromImage($imagePath)
    {
        return static::$map[$imagePath] ??= $imagePath;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereBelongsTo(Filament::auth()->user());
    }
}
