<?php

namespace App\Filament\Resources\ProfileResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

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
                        SpatieMediaLibraryFileUpload::make('banner')
                            ->label('Image bannière')
                            ->collection('banner')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Photo de profil')
                            ->collection('avatar')
                            ->image()
                            ->imageEditor()
                            ->avatar()
                            ->columnSpanFull(),
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
            ]);
    }
}

