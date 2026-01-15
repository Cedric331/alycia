<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Carbon\Carbon;
use Spatie\Image\Enums\Fit;

class Profile extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'biography',
        'is_online',
        'photos_count',
        'videos_count',
        'likes_count',
        'action_label',
        'online_from',
        'online_to',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'online_from' => 'string',
        'online_to' => 'string',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar_preview')
            ->fit(Fit::Crop, 300, 300)
            ->nonQueued()
            ->performOnCollections('avatar');
    
        $this->addMediaConversion('banner_preview')
            ->fit(Fit::Crop, 1200, 450)
            ->nonQueued()
            ->performOnCollections('banner');
    }
    

    public function isWithinOnlineHours(?Carbon $now = null, ?string $tz = null): bool
    {
        if (! $this->online_from || ! $this->online_to) {
            return false;
        }

        $now = ($now ?? now())->copy();
        if ($tz) {
            $now->setTimezone($tz);
        }

        $toMinutes = fn (string $time) => (int) Carbon::createFromFormat('H:i:s', strlen($time) === 5 ? "{$time}:00" : $time)
            ->format('H') * 60
            + (int) Carbon::createFromFormat('H:i:s', strlen($time) === 5 ? "{$time}:00" : $time)->format('i');

        $start = $toMinutes($this->online_from);
        $end   = $toMinutes($this->online_to); 
        $current = ((int) $now->format('H')) * 60 + (int) $now->format('i');

        if ($start <= $end) {
            return $current >= $start && $current <= $end;
        }

        return $current >= $start || $current <= $end;
    }
}

