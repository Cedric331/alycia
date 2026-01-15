<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Carbon\Carbon;

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')
            ->singleFile();

        $this->addMediaCollection('avatar')
            ->singleFile();
    }

    /**
     * Get the default profile (singleton pattern)
     */
    public static function getDefault(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Alycia',
                'biography' => "C'est ici où je post le 🔞",
                'is_online' => false,
                'photos_count' => 0,
                'videos_count' => 0,
                'likes_count' => 0,
                'action_label' => "S'abonner au VIP d'alycia",
            ]
        );
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

