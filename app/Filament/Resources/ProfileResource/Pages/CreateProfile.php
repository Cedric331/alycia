<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Profile;
use Filament\Notifications\Notification;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;
}

