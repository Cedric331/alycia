<?php

namespace App\Filament\Resources\PostResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Section;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Contenu')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Contenu')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'photo' => 'Photo',
                                'video' => 'Vidéo',
                                'live' => 'Live',
                            ])
                            ->required()
                            ->default('photo'),
                        Forms\Components\TextInput::make('duration')
                            ->label('Durée (vidéo / live)')
                            ->helperText('Exemple : 12:34')
                            ->maxLength(20)
                            ->visible(fn (callable $get) => in_array($get('type'), ['video', 'live'])),
                        Forms\Components\TextInput::make('order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Médias')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('media')
                            ->label('Média')
                            ->collection('media')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                Section::make('Statistiques et visibilité')
                    ->schema([
                        Forms\Components\TextInput::make('likes_count')
                            ->label('Nombre de likes')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true),
                        Forms\Components\Toggle::make('is_blurred')
                            ->label('Flouter l\'image')
                            ->helperText('Si activé, l\'image sera floutée avec un cadenas en front')
                            ->default(true),
                        Forms\Components\Toggle::make('is_live')
                            ->label('En Live')
                            ->helperText('Affiche un badge "LIVE" sur les vidéos')
                            ->default(false)
                            ->visible(fn (callable $get) => in_array($get('type'), ['video', 'live'])),
                    ])
                    ->columns(2),
            ]);
    }
}

