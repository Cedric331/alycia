<?php

namespace App\Filament\Resources\ProfileResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('biography')
                            ->label('Biographie')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_online')
                            ->label('En ligne')
                            ->default(false),
                        Forms\Components\TextInput::make('action_label')
                            ->label('Label du bouton d\'action')
                            ->placeholder("S'abonner au VIP d'alycia")
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Médias')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('banner_media')
                        ->collection('banner')
                        ->conversion('banner_preview')
                        ->image()
                        ->imageEditor(),
                    
                    SpatieMediaLibraryFileUpload::make('avatar_media')
                        ->collection('avatar')
                        ->conversion('avatar_preview')
                        ->image()
                        ->imageEditor()
                        ->avatar(),
                    ]),

                Section::make('Indicateurs')
                    ->description('Ces valeurs sont gérées manuellement depuis l\'admin')
                    ->schema([
                        Forms\Components\TextInput::make('photos_count')
                            ->label('Nombre de photos')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('videos_count')
                            ->label('Nombre de vidéos')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('likes_count')
                            ->label('Nombre de likes')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Horaires d\'activité')
                    ->schema([
                        TimePicker::make('online_from')
                        ->label('Heure début')
                        ->seconds(false)
                        ->nullable()
                        ->rule(function (Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($value && ! $get('online_to')) {
                                    $fail('L’heure de fin est obligatoire.');
                                }
                            };
                        }),
                        TimePicker::make('online_to')
                        ->label('Heure fin')
                        ->seconds(false)
                        ->nullable()
                        ->rule(function (Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($value && ! $get('online_from')) {
                                    $fail('L’heure de début est obligatoire.');
                                }
                            };
                        }),
                    ])
                    ->columns(2),
            ]);
    }
}

